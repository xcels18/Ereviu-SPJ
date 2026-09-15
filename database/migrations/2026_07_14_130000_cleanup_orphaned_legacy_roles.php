<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Role lama sisa dari skema role sebelum konsolidasi (lihat migration
     * 2026_07_14_050000_consolidate_user_roles) tidak lagi dipakai oleh user
     * mana pun dan tidak dirujuk oleh kode aplikasi. Dihapus untuk menjaga
     * konsistensi data.
     */
    private array $orphanedRoles = [
        'admin_inspektorat',
        'auditor',
        'irban_1',
        'irban_2',
        'irban_3',
        'irban_4',
    ];

    public function up(): void
    {
        foreach ($this->orphanedRoles as $name) {
            $role = Role::where('name', $name)->first();
            if ($role && $role->users()->count() === 0) {
                $role->delete();
            }
        }
    }

    public function down(): void
    {
        foreach ($this->orphanedRoles as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }
};
