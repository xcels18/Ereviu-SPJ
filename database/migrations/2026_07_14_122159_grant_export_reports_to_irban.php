<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Menu "Cetak Laporan" (export_reports) sebelumnya hanya tampil untuk role
     * admin & inspektur. Role irban sekarang juga diberi akses yang sama.
     */
    public function up(): void
    {
        Permission::firstOrCreate(['name' => 'export_reports']);

        $irban = Role::where('name', 'irban')->first();
        if ($irban) {
            $irban->givePermissionTo('export_reports');
        }
    }

    public function down(): void
    {
        $irban = Role::where('name', 'irban')->first();
        if ($irban) {
            $irban->revokePermissionTo('export_reports');
        }
    }
};
