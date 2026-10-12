<?php

namespace App\Jobs;

use App\Services\DatabaseBackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BuildDatabaseBackup implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(public string $id)
    {
        $this->onConnection('spj_compile')->onQueue('spj-compile');
    }

    public function handle(DatabaseBackupService $service): void
    {
        $service->build($this->id);
    }

    public function failed(?\Throwable $exception): void
    {
        app(DatabaseBackupService::class)->fail($this->id);
    }
}
