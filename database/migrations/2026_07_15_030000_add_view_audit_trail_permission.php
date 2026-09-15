<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Menu baru "Audit Trail" (rekam jejak seluruh aktivitas user untuk
     * keamanan & akuntabilitas) hanya dapat diakses oleh role admin.
     */
    public function up(): void
    {
        Permission::firstOrCreate(['name' => 'view_audit_trail']);

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo('view_audit_trail');
        }
    }

    public function down(): void
    {
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->revokePermissionTo('view_audit_trail');
        }
    }
};
