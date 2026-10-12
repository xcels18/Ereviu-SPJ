<?php

namespace App\Http\Controllers;

use App\Jobs\BuildDatabaseBackup;
use App\Services\ActivityLogger;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class DatabaseBackupController extends Controller
{
    public function index(DatabaseBackupService $service)
    {
        $root = storage_path('app/private/database-backups');
        $backups = collect(is_dir($root) ? File::directories($root) : [])
            ->filter(fn ($dir) => is_file($dir.'/status.json'))
            ->map(fn ($dir) => $service->read(basename($dir)))
            ->filter(fn ($item) => $item['user_id'] === auth()->id() && $item['expires_at'] > time())
            ->sortByDesc('created_at')->values();
        return view('database-backups.index', compact('backups'));
    }

    public function store(Request $request, DatabaseBackupService $service)
    {
        return Cache::lock('database-backup-admission', 10)->block(3, function () use ($request, $service) {
            $root = storage_path('app/private/database-backups');
            foreach (is_dir($root) ? File::directories($root) : [] as $dir) {
                if (! is_file($dir.'/status.json')) continue;
                $item = $service->read(basename($dir));
                abort_if(in_array($item['status'], ['queued', 'processing'], true) && $item['expires_at'] > time(), 409, 'Masih ada backup aktif. Tunggu selesai.');
            }
            $id = $service->create($request->user()->id);
            try { BuildDatabaseBackup::dispatch($id); } catch (\Throwable $e) { $service->fail($id); throw $e; }
            ActivityLogger::log('backup', 'database', 'Mengantrekan backup database '.$id);
            return back()->with('success', 'Backup diantrekan. Muat ulang untuk memeriksa hasil.');
        });
    }

    public function download(Request $request, string $id, DatabaseBackupService $service)
    {
        $item = $service->read($id);
        abort_unless($item['user_id'] === $request->user()->id, 403);
        abort_unless($item['status'] === 'completed' && $item['expires_at'] > time(), 410);
        ActivityLogger::log('download', 'database', 'Mengunduh backup database '.$id);
        return response()->download($service->directory($id).'/database.sql', 'database-'.$id.'.sql', ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
