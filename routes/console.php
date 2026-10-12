<?php

use App\Models\SpjExport;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('spj-exports:prune', function () {
    // A hard-killed worker may not run failed(); mark stale runs explicitly.
    SpjExport::whereIn('status', ['preparing', 'archiving', 'cancelling'])
        ->where('started_at', '<', now()->subMinutes(70))
        ->update(['status' => 'failed', 'error' => 'Worker terhenti atau melewati batas waktu.', 'expires_at' => now()->addHour()]);
    SpjExport::where('status', 'queued')->where('created_at', '<', now()->subDay())
        ->update(['status' => 'failed', 'error' => 'Antrean tidak diproses selama 24 jam. Periksa worker.', 'expires_at' => now()->addHour()]);
    $count = 0;
    foreach (SpjExport::whereIn('status', ['completed', 'cancelled', 'failed'])->where('expires_at', '<=', now())->lazyById(100) as $export) {
        if (is_dir($export->directory()) && ! File::deleteDirectory($export->directory())) {
            $this->error('Gagal membersihkan paket '.$export->id);

            continue;
        }
        $export->update(['status' => 'expired', 'parts' => []]);
        $count++;
    }
    $root = storage_path('app/private/spj-exports');
    if (is_dir($root)) {
        foreach (File::directories($root) as $directory) {
            $id = basename($directory);
            if (Str::isUuid($id) && filemtime($directory) < now()->subDay()->timestamp && ! SpjExport::whereKey($id)->exists()) {
                File::deleteDirectory($directory);
            }
        }
    }
    $this->info("{$count} paket kedaluwarsa dibersihkan.");
})->purpose('Bersihkan paket SPJ kedaluwarsa dan tandai worker yang terhenti');

Schedule::command('spj-exports:prune')->hourly()->withoutOverlapping();

Artisan::command('spj:clean-deleted-files', function () {
    foreach (DB::table('pending_file_deletions')->orderBy('id')->lazyById(100) as $row) {
        $disk = Storage::disk($row->disk);
        if (! $disk->exists($row->path) || $disk->delete($row->path)) {
            DB::table('pending_file_deletions')->where('id', $row->id)->delete();
        } else {
            $this->error('Pembersihan berkas gagal: antrean '.$row->id);
        }
    }
})->purpose('Bersihkan berkas setelah penghapusan metadata berhasil');
Schedule::command('spj:clean-deleted-files')->everyFiveMinutes()->withoutOverlapping();

Artisan::command('app:ensure-key', function () {
    if (config('app.key')) {
        $this->info('APP_KEY yang ada dipertahankan.');

        return 0;
    }

    return $this->call('key:generate');
})->purpose('Buat kunci hanya untuk instalasi baru tanpa APP_KEY');

Artisan::command('spj:operations-status', function () {
    $this->table(['Status', 'Paket'], SpjExport::selectRaw('status, count(*) as total')->groupBy('status')->get()->map(fn ($row) => [$row->status, $row->total]));
    $this->line('Pending cleanup: '.DB::table('pending_file_deletions')->count());
    $this->line('Failed jobs: '.DB::table('failed_jobs')->count());
    $this->line('Disk bebas GiB: '.round(disk_free_space(storage_path()) / 1073741824, 2));
    $stale = SpjExport::where('status', 'queued')->where('created_at', '<', now()->subMinutes(15))->count();
    if ($stale) {
        $this->error($stale.' paket menunggu lebih dari 15 menit. Periksa Supervisor.');

        return 1;
    }

    return 0;
})->purpose('Ringkasan operasional untuk monitoring server');

Artisan::command('database-backups:prune', function () {
    $root = storage_path('app/private/database-backups');
    foreach (is_dir($root) ? File::directories($root) : [] as $dir) {
        if (! is_file($dir.'/status.json')) continue;
        $item = json_decode(file_get_contents($dir.'/status.json'), true);
        if (($item['expires_at'] ?? PHP_INT_MAX) <= time()) File::deleteDirectory($dir);
    }
})->purpose('Bersihkan backup database setelah 24 jam');
Schedule::command('database-backups:prune')->hourly()->withoutOverlapping();
