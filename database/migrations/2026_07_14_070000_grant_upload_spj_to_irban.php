<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Halaman upload SPJ kini merangkap proses reviu (catatan reviu per akun belanja),
     * sehingga irban perlu bisa mengakses menu "Upload SPJ" (dibatasi wilayah kerjanya).
     */
    public function up(): void
    {
        $irban = Role::where('name', 'irban')->first();
        if ($irban) {
            $irban->givePermissionTo('upload_spj');
        }
    }

    public function down(): void
    {
        $irban = Role::where('name', 'irban')->first();
        if ($irban) {
            $irban->revokePermissionTo('upload_spj');
        }
    }
};
