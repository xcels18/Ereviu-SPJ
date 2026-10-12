<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_routes_require_admin_and_download_owner(): void
    {
        Role::findOrCreate('admin');
        $owner = User::factory()->create();
        $owner->assignRole('admin');
        $other = User::factory()->create();
        $this->actingAs($other)->get(route('database-backups.index'))->assertForbidden();
        $this->actingAs($other)->post(route('database-backups.store'))->assertForbidden();
        $service = app(DatabaseBackupService::class);
        $id = (string) Str::uuid();
        File::ensureDirectoryExists($service->directory($id), 0700);
        try {
            $service->write($id, ['id'=>$id, 'user_id'=>$owner->id, 'status'=>'completed', 'created_at'=>now()->toIso8601String(), 'expires_at'=>time()+3600, 'bytes'=>10, 'sha256'=>'test']);
            file_put_contents($service->directory($id).'/database.sql', 'test dump');
            $other->assignRole('admin');
            $this->actingAs($other)->get(route('database-backups.download', $id))->assertForbidden();
            $this->actingAs($owner)->get(route('database-backups.download', $id))->assertOk();
            $this->get(route('database-backups.index'))->assertOk()->assertSee('Unduh SQL');
            $item = $service->read($id);
            $service->write($id, array_merge($item, ['expires_at'=>time()-1]));
            $this->get(route('database-backups.download', $id))->assertStatus(410);
        } finally { File::deleteDirectory($service->directory($id)); }
    }

    public function test_failed_backup_removes_credentials_and_partial_file(): void
    {
        $service = app(DatabaseBackupService::class);
        $id = (string) Str::uuid();
        File::ensureDirectoryExists($service->directory($id), 0700);
        try {
            $service->write($id, ['status'=>'processing']);
            file_put_contents($service->directory($id).'/client.cnf', 'test');
            file_put_contents($service->directory($id).'/database.sql.tmp', 'partial');
            $service->fail($id);
            $this->assertSame('failed', $service->read($id)['status']);
            $this->assertFileDoesNotExist($service->directory($id).'/client.cnf');
            $this->assertFileDoesNotExist($service->directory($id).'/database.sql.tmp');
        } finally { File::deleteDirectory($service->directory($id)); }
    }
}
