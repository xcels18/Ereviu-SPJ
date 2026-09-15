<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Skema role: 3 role internal Inspektorat (admin, irban, inspektur)
        // + operator_irban (petugas upload SPJ yang menginduk ke Irban tertentu)
        // + 2 role sisi OPD (opd_admin, kepala_opd). Skema ini konsisten dengan
        // yang dipakai di seluruh aplikasi (lihat penggunaan hasRole() di controller).
        $roles = [
            'admin',
            'irban',
            'operator_irban',
            'inspektur',
            'opd_admin',
            'kepala_opd',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create permissions for OPD management
        Permission::firstOrCreate(['name' => 'manage_opd']);
        Permission::firstOrCreate(['name' => 'view_opd']);

        // Permissions for SP2D
        Permission::firstOrCreate(['name' => 'manage_sp2d']);
        Permission::firstOrCreate(['name' => 'view_sp2d']);
        Permission::firstOrCreate(['name' => 'import_sp2d']);

        // Permissions for SPJ
        Permission::firstOrCreate(['name' => 'manage_spj']);
        Permission::firstOrCreate(['name' => 'view_spj']);
        Permission::firstOrCreate(['name' => 'upload_spj']);
        Permission::firstOrCreate(['name' => 'review_spj']);

        // Permissions for schedules
        Permission::firstOrCreate(['name' => 'manage_schedule']);
        Permission::firstOrCreate(['name' => 'view_schedule']);

        // Permissions for review guides
        Permission::firstOrCreate(['name' => 'manage_review_guide']);
        Permission::firstOrCreate(['name' => 'view_review_guide']);

        // Permissions for reports
        Permission::firstOrCreate(['name' => 'view_reports']);
        Permission::firstOrCreate(['name' => 'export_reports']);

        // Permissions for auditor assignments
        Permission::firstOrCreate(['name' => 'manage_auditor_assignment']);

        // Permission for user management
        Permission::firstOrCreate(['name' => 'manage_users']);

        // Permission for compiling uploaded SPJ files (admin only)
        Permission::firstOrCreate(['name' => 'compile_spj']);

        // Permission for audit trail (admin only)
        Permission::firstOrCreate(['name' => 'view_audit_trail']);

        // Assign permissions to roles
        // Admin (Inspektorat) - full access
        $admin = Role::findByName('admin');
        $admin->syncPermissions([
            'manage_opd', 'view_opd',
            'manage_sp2d', 'view_sp2d', 'import_sp2d',
            'manage_spj', 'view_spj', 'upload_spj', 'review_spj',
            'manage_schedule', 'view_schedule',
            'manage_review_guide', 'view_review_guide',
            'view_reports', 'export_reports',
            'manage_auditor_assignment',
            'manage_users',
            'compile_spj',
            'view_audit_trail',
        ]);

        // Irban - dapat mereviu & mengunggah SPJ untuk wilayah kerja yang ditugaskan
        $irban = Role::findByName('irban');
        $irban->syncPermissions([
            'view_opd', 'view_sp2d',
            'view_spj', 'upload_spj', 'review_spj',
            'view_schedule', 'view_review_guide',
            'view_reports', 'export_reports',
        ]);

        // Operator Irban - hanya bertugas mengunggah SPJ untuk wilayah kerja
        // Irban induknya (tanpa hak mereviu maupun akses menu lain).
        $operatorIrban = Role::findByName('operator_irban');
        $operatorIrban->syncPermissions([
            'upload_spj',
        ]);

        // Inspektur - dapat mereviu & melihat laporan seluruh wilayah
        $inspektur = Role::findByName('inspektur');
        $inspektur->syncPermissions([
            'view_opd', 'view_sp2d',
            'view_spj', 'review_spj',
            'view_schedule', 'view_review_guide',
            'view_reports', 'export_reports',
        ]);

        // OPD Admin - can manage SPJ for their OPD
        $opdAdmin = Role::findByName('opd_admin');
        $opdAdmin->syncPermissions([
            'view_opd',
            'view_sp2d',
            'manage_spj', 'view_spj', 'upload_spj',
            'view_schedule',
            'view_review_guide',
            'view_reports',
        ]);

        // Kepala OPD - can view reports
        $kepalaOpd = Role::findByName('kepala_opd');
        $kepalaOpd->syncPermissions([
            'view_opd',
            'view_sp2d',
            'view_spj',
            'view_schedule',
            'view_review_guide',
            'view_reports', 'export_reports',
        ]);
    }
}