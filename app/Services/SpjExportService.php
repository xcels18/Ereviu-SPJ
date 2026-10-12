<?php

namespace App\Services;

use App\Exceptions\SpjExportException as RuntimeException;
use App\Models\Sp2d;
use App\Models\SpjExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class SpjExportService
{
    public function sourcePath(string $relative): string
    {
        foreach (['local', 'public'] as $disk) {
            $root = realpath(Storage::disk($disk)->path(''));
            $path = realpath(Storage::disk($disk)->path($relative));
            if ($root && $path && str_starts_with($path, $root.DIRECTORY_SEPARATOR) && is_file($path) && is_readable($path)) {
                return $path;
            }
        }
        throw new RuntimeException('Dokumen sumber hilang atau tidak dapat dibaca.');
    }

    private function check(SpjExport $export, int $needed = 0): void
    {
        if ($export->fresh()?->status === 'cancelling') {
            throw new RuntimeException('cancelled');
        }
        $free = disk_free_space($export->directory());
        if ($free === false || $free < $needed + config('spj_compile.min_free_bytes')) {
            throw new RuntimeException('Ruang penyimpanan sementara tidak mencukupi.');
        }
        $export->touch();
    }

    public function build(SpjExport $export): void
    {
        $dir = $export->directory();
        if (! is_dir($dir) && ! mkdir($dir, 0700, true)) {
            throw new RuntimeException('Tidak dapat membuat direktori arsip.');
        }
        if (! SpjExport::whereKey($export->id)->where('status', 'queued')->update(['status' => 'preparing', 'started_at' => now()])) {
            return;
        }
        $export->refresh();
        $manifest = fopen($dir.'/manifest.jsonl', 'wb');
        if (! $manifest) {
            throw new RuntimeException('Tidak dapat membuat manifest.');
        }
        $zip = null;
        try {
            $compiler = app(SpjCompileService::class);
            $query = Sp2d::where('opd_id', $export->opd_id)->whereHas('spj');
            $selection = $export->selection;
            if (! ($selection['all'] ?? false)) {
                $query->whereIn('id', $selection['sp2d_ids']);
            }
            $count = 0;
            $bytes = 0;
            foreach ($query->lazyById(50) as $sp2d) {
                $this->check($export);
                $ids = $selection['budget_codes'][$sp2d->id] ?? $sp2d->budgetCodes()->pluck('id')->all();
                if (! $ids && $sp2d->budgetCodes()->doesntExist()) {
                    $ids = [0];
                }
                $documents = $compiler->selectedDocuments($sp2d, $ids);
                $folder = (Str::slug($export->opd_name) ?: 'opd').'/'.$sp2d->id.'-'.Str::slug($sp2d->nomor_sp2d);
                if (($selection['folder_layout'] ?? 'opd_sp2d') === 'year_quarter') {
                    $folder = (Str::slug($export->opd_name) ?: 'opd').'/'.$sp2d->tanggal->format('Y').'/Triwulan-'.$sp2d->triwulan.'/'.$sp2d->id.'-'.Str::slug($sp2d->nomor_sp2d);
                }
                $accountFolders = $sp2d->budgetCodes->keyBy('id');
                $sources = [];
                if ($export->mode === 'merged') {
                    $inputBytes = 0;
                    foreach ($documents as $doc) {
                        $inputBytes += filesize($this->sourcePath($doc->file_path));
                    }
                    $this->check($export, $inputBytes * 3);
                    $path = $compiler->compileSp2d($sp2d, $ids, $dir);
                    if ($path) {
                        $sources[] = ['path' => $path, 'name' => $folder.'/gabungan.pdf', 'temporary' => true];
                    }
                } else {
                    foreach ($documents as $doc) {
                        $name = preg_replace('/[^\pL\pN._ -]/u', '_', basename(str_replace('\\', '/', $doc->file_name)));
                        $documentFolder = $doc->document_type;
                        if ($doc->document_type === 'bukti_belanja') {
                            $account = $accountFolders->get($doc->sp2d_budget_code_id);
                            $documentFolder .= $account
                                ? '/'.$account->id.'-'.(Str::slug($account->kode_belanja) ?: 'akun')
                                : '/gabungan';
                        }
                        $sources[] = ['path' => $this->sourcePath($doc->file_path), 'name' => $folder.'/'.$documentFolder.'/'.$doc->id.'-'.mb_substr($name ?: 'dokumen', 0, 140), 'temporary' => false];
                    }
                }
                foreach ($sources as $source) {
                    $source['size'] = filesize($source['path']);
                    $source['mtime'] = filemtime($source['path']);
                    $line = json_encode($source, JSON_THROW_ON_ERROR)."\n";
                    if (fwrite($manifest, $line) !== strlen($line)) {
                        throw new RuntimeException('Gagal menulis manifest.');
                    }
                    $bytes += $source['size'];
                    $count++;
                }
            }
            fclose($manifest);
            $manifest = null;
            if (! $count) {
                throw new RuntimeException('Tidak ada dokumen yang dapat diarsipkan.');
            }
            $this->check($export, (int) ceil($bytes * 1.05));
            $export->update(['status' => 'archiving', 'total_files' => $count, 'total_bytes' => $bytes]);
            $manifest = fopen($dir.'/manifest.jsonl', 'rb');
            $partSources = [];
            $partBytes = 0;
            $parts = [];
            $done = 0;
            $doneBytes = 0;
            $flush = function () use (&$zip, &$partSources, &$partBytes, &$parts, &$done, &$doneBytes, $dir, $export): void {
                if (! $partSources) {
                    return;
                }
                $this->check($export, (int) ceil($partBytes * 1.05));
                $number = count($parts) + 1;
                $filename = sprintf('bagian-%03d.zip', $number);
                $zip = new ZipArchive;
                if ($zip->open($dir.'/'.$filename.'.tmp', ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                    throw new RuntimeException('Gagal membuat ZIP.');
                }
                foreach ($partSources as $source) {
                    clearstatcache(true, $source['path']);
                    if (! is_file($source['path']) || filesize($source['path']) !== $source['size'] || filemtime($source['path']) !== $source['mtime']) {
                        throw new RuntimeException('Dokumen berubah selama proses. Buat ulang arsip.');
                    }
                    if (! $zip->addFile($source['path'], $source['name'])) {
                        throw new RuntimeException('Gagal menambahkan dokumen.');
                    }
                    if (! $zip->setCompressionName($source['name'], $export->compression === 'fast' ? ZipArchive::CM_STORE : ZipArchive::CM_DEFLATE, 1)) {
                        throw new RuntimeException('Gagal mengatur kompresi ZIP.');
                    }
                }
                // libzip reads from disk during close; callbacks report actual work and cancellation.
                $lastCheck = 0;
                $zip->registerCancelCallback(function () use ($export, &$lastCheck): int {
                    if (time() - $lastCheck < 2) {
                        return 0;
                    }
                    $lastCheck = time();

                    return $export->fresh()?->status === 'cancelling' ? 1 : 0;
                });
                if (! $zip->close()) {
                    throw new RuntimeException('Gagal menyelesaikan ZIP atau proses dibatalkan.');
                }
                $zip = null;
                foreach ($partSources as $source) {
                    clearstatcache(true, $source['path']);
                    if (! is_file($source['path']) || filesize($source['path']) !== $source['size'] || filemtime($source['path']) !== $source['mtime']) {
                        throw new RuntimeException('Dokumen berubah selama proses. Buat ulang arsip.');
                    }
                }
                $this->check($export);
                if (! rename($dir.'/'.$filename.'.tmp', $dir.'/'.$filename)) {
                    throw new RuntimeException('Gagal menerbitkan ZIP.');
                }
                $parts[] = ['name' => $filename, 'bytes' => filesize($dir.'/'.$filename), 'files' => count($partSources)];
                $done += count($partSources);
                $doneBytes += $partBytes;
                $export->update(['parts' => $parts, 'processed_files' => $done, 'processed_bytes' => $doneBytes]);
                foreach ($partSources as $source) {
                    if ($source['temporary']) {
                        unlink($source['path']);
                    }
                }
                $partSources = [];
                $partBytes = 0;
            };
            while (($line = fgets($manifest)) !== false) {
                $source = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
                if ($partSources && ($partBytes + $source['size'] > config('spj_compile.part_bytes') || count($partSources) >= config('spj_compile.part_files'))) {
                    $flush();
                }
                $partSources[] = $source;
                $partBytes += $source['size'];
            }
            $flush();
            $this->check($export);
            // Conditional update avoids turning a concurrent cancel into success.
            SpjExport::whereKey($export->id)->where('status', 'archiving')->update(['status' => 'completed', 'expires_at' => now()->addHours(config('spj_compile.retention_hours'))]);
            if ($export->fresh()->status === 'cancelling') {
                throw new RuntimeException('cancelled');
            }
        } catch (\Throwable $e) {
            report($e);
            $cancelled = $export->fresh()?->status === 'cancelling';
            $safeMessage = $e instanceof RuntimeException ? $e->getMessage() : 'Proses gagal. Periksa dokumen sumber dan kapasitas worker.';
            $export->update(['status' => $cancelled ? 'cancelled' : 'failed', 'error' => $cancelled ? null : $safeMessage, 'expires_at' => now()->addHour()]);
        } finally {
            if (is_resource($manifest)) {
                fclose($manifest);
            }
            if ($zip) {
                try {
                    @$zip->unchangeAll();
                    @$zip->close();
                } catch (\Throwable $cleanupError) {
                    report($cleanupError);
                }
            }
            if ($export->fresh()->status !== 'completed') {
                foreach (glob($dir.'/*') ?: [] as $path) {
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
            }
        }
    }
}
