<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Konsolidasi role internal Inspektorat menjadi 3 jenis: admin, irban, inspektur.
     * Role sisi OPD (opd_admin, kepala_opd) tidak diubah.
     */
    public function up(): void
    {
        // Pastikan semua permission yang dirujuk di bawah ini sudah ada.
        // Migrasi ini sebelumnya mengasumsikan permission sudah dibuat lewat
        // seeder, sehingga gagal (PermissionDoesNotExist) saat dijalankan
        // dari kondisi kosong (mis. migrate:fresh tanpa seed, atau test DB).
        foreach ([
            'manage_opd', 'view_opd',
            'manage_sp2d', 'view_sp2d', 'import_sp2d',
            'manage_spj', 'view_spj', 'upload_spj', 'review_spj',
            'manage_schedule', 'view_schedule',
            'manage_review_guide', 'view_review_guide',
            'view_reports', 'export_reports',
            'manage_auditor_assignment',
            'manage_users',
        ] as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $irban = Role::firstOrCreate(['name' => 'irban']);
        $inspektur = Role::firstOrCreate(['name' => 'inspektur']);

        $admin->syncPermissions([
            'manage_opd', 'view_opd',
            'manage_sp2d', 'view_sp2d', 'import_sp2d',
            'manage_spj', 'view_spj', 'upload_spj', 'review_spj',
            'manage_schedule', 'view_schedule',
            'manage_review_guide', 'view_review_guide',
            'view_reports', 'export_reports',
            'manage_auditor_assignment',
            'manage_users',
        ]);

        $irban->syncPermissions([
            'view_opd', 'view_sp2d',
            'view_spj', 'review_spj',
            'view_schedule', 'view_review_guide',
            'view_reports', 'export_reports',
        ]);

        $inspektur->syncPermissions([
            'view_opd', 'view_sp2d',
            'view_spj', 'review_spj',
            'view_schedule', 'view_review_guide',
            'view_reports', 'export_reports',
        ]);

        // Migrasikan user yang masih memakai role lama ke skema baru
        $oldToNew = [
            'admin_inspektorat' => 'admin',
            'irban_1' => 'irban',
            'irban_2' => 'irban',
            'irban_3' => 'irban',
            'irban_4' => 'irban',
            'auditor' => 'irban',
        ];

        foreach ($oldToNew as $oldRole => $newRole) {
            User::where('role', $oldRole)->get()->each(function (User $user) use ($newRole) {
                $user->update(['role' => $newRole]);
                $user->syncRoles([$newRole]);
            });
        }
    }

    public function down(): void
    {
        // Data mapping tidak sepenuhnya reversibel 1:1; revert permission tambahan saja.
        Permission::where('name', 'manage_users')->delete();
    }
};
