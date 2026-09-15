<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom penanda "induk" untuk role Operator Irban. Operator
     * Irban tidak memiliki wilayah kerja (OPD) sendiri, melainkan mengikuti
     * wilayah kerja dari Irban tertentu yang dipilih saat pembuatan akun.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('parent_irban_id')->nullable()->after('opd_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_irban_id');
        });
    }
};
