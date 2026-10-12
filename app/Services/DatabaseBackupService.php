<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    public function directory(string $id): string
    {
        abort_unless(Str::isUuid($id), 404);
        return storage_path('app/private/database-backups/'.$id);
    }

    public function read(string $id): array
    {
        $path = $this->directory($id).'/status.json';
        abort_unless(is_file($path), 404);
        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    public function write(string $id, array $data): void
    {
        $path = $this->directory($id).'/status.json';
        file_put_contents($path.'.tmp', json_encode($data, JSON_THROW_ON_ERROR), LOCK_EX);
        chmod($path.'.tmp', 0600);
        rename($path.'.tmp', $path);
    }

    public function create(int $userId): string
    {
        abort_unless(in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true), 422, 'Backup ini mendukung MySQL/MariaDB.');
        $id = (string) Str::uuid();
        File::ensureDirectoryExists($this->directory($id), 0700);
        $this->write($id, ['id' => $id, 'user_id' => $userId, 'status' => 'queued', 'created_at' => now()->toIso8601String(), 'expires_at' => now()->addDay()->timestamp]);
        return $id;
    }

    public function fail(string $id): void
    {
        $dir = $this->directory($id);
        foreach (['client.cnf', 'database.sql.tmp'] as $file) {
            if (is_file($dir.'/'.$file)) unlink($dir.'/'.$file);
        }
        $data = $this->read($id);
        $this->write($id, array_merge($data, ['status' => 'failed', 'error' => 'Backup gagal. Periksa utilitas dump, hak akses database, dan ruang disk server.']));
    }

    public function build(string $id): void
    {
        $data = $this->read($id);
        if ($data['status'] !== 'queued' || $data['expires_at'] <= time()) return;
        $dir = $this->directory($id);
        $this->write($id, array_merge($data, ['status' => 'processing']));
        try {
            if (disk_free_space($dir) < config('database_backup.min_free_bytes')) throw new RuntimeException('Disk');
            $config = DB::connection()->getConfig();
            // Use a private option file: credentials never enter process arguments or logs.
            $quote = fn ($value) => '"'.str_replace(["\\", '"', "\n", "\r"], ["\\\\", '\\"', '\\n', '\\r'], (string) $value).'"';
            $options = "[client]\n";
            foreach (['host' => 'host', 'port' => 'port', 'username' => 'user', 'password' => 'password', 'unix_socket' => 'socket'] as $key => $option) {
                if (($config[$key] ?? '') !== '') $options .= $option.'='.$quote($config[$key])."\n";
            }
            $oldMask = umask(0077);
            try { file_put_contents($dir.'/client.cnf', $options); } finally { umask($oldMask); }
            $args = [config('database_backup.binary'), '--defaults-extra-file='.$dir.'/client.cnf', '--single-transaction', '--quick', '--skip-lock-tables', '--hex-blob', '--no-tablespaces', '--result-file='.$dir.'/database.sql.tmp'];
            if ($config['driver'] === 'mysql') $args[] = '--set-gtid-purged=OFF';
            $args[] = '--databases';
            $args[] = $config['database'];
            $process = new Process($args);
            $process->setTimeout(3500);
            $process->disableOutput();
            $process->run();
            if (! $process->isSuccessful() || ! is_file($dir.'/database.sql.tmp') || filesize($dir.'/database.sql.tmp') === 0) throw new RuntimeException('Dump');
            chmod($dir.'/database.sql.tmp', 0600);
            rename($dir.'/database.sql.tmp', $dir.'/database.sql');
            $this->write($id, array_merge($data, ['status' => 'completed', 'bytes' => filesize($dir.'/database.sql'), 'sha256' => hash_file('sha256', $dir.'/database.sql')]));
        } catch (\Throwable $e) {
            $this->fail($id);
        } finally {
            if (is_file($dir.'/client.cnf')) unlink($dir.'/client.cnf');
        }
    }
}
