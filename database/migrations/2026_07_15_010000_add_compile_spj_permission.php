<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Menu baru "Compile SPJ" (kompilasi file SPJ yang sudah diupload menjadi
     * satu PDF gabungan per SP2D, disusun dalam folder & diunduh sebagai ZIP)
     * hanya dapat diakses oleh role admin.
     */
    public function up(): void
    {
        Permission::firstOrCreate(['name' => 'compile_spj']);

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo('compile_spj');
        }
    }

    public function down(): void
    {
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->revokePermissionTo('compile_spj');
        }
    }
};
