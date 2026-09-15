<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyimpan catatan reviu per akun belanja yang diisi bersamaan
     * dengan proses upload SPJ.
     */
    public function up(): void
    {
        Schema::table('spj_findings', function (Blueprint $table) {
            $table->foreignId('sp2d_budget_code_id')->nullable()->after('spj_id')
                ->constrained('sp2d_budget_codes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('spj_findings', function (Blueprint $table) {
            $table->dropForeign(['sp2d_budget_code_id']);
            $table->dropColumn('sp2d_budget_code_id');
        });
    }
};
