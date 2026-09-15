<?php

namespace App\Jobs;

use App\Models\SpjExport;
use App\Services\SpjExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

class BuildSpjExport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 1;

    public bool $failOnTimeout = true;

    public function __construct(public string $exportId)
    {
        $this->onConnection('spj_compile')->onQueue('spj-compile');
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('spj-export-'.$this->exportId))->dontRelease()->expireAfter(3650)];
    }

    public function handle(SpjExportService $service): void
    {
        $export = SpjExport::find($this->exportId);
        if (! $export || $export->status !== 'queued') {
            return;
        }
        $service->build($export);
    }

    public function failed(?Throwable $exception): void
    {
        SpjExport::whereKey($this->exportId)->whereNotIn('status', ['completed', 'cancelled', 'expired'])
            ->update(['status' => 'failed', 'error' => 'Proses gagal atau melewati batas waktu. Periksa kapasitas server dan buat paket lebih kecil.', 'expires_at' => now()->addHour()]);
    }
}
