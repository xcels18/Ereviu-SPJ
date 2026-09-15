<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('opd_id')->nullable()->after('id')->constrained('opds')->onDelete('set null');
            $table->string('role', 50)->default('opd_admin')->after('password'); // admin_inspektorat, auditor, irban_1, irban_2, irban_3, irban_4, opd_admin, kepala_opd
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['opd_id']);
            $table->dropColumn(['opd_id', 'role']);
        });
    }
};