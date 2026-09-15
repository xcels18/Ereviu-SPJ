<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spj_findings', function (Blueprint $table) {
            // Catatan: tidak memakai ->after('sp2d_budget_code_id') karena kolom
            // tsb baru ditambahkan oleh migration 2026_07_14_060000 (berjalan
            // setelah migration ini). MySQL memvalidasi kolom "after" secara
            // ketat saat ALTER TABLE, berbeda dengan SQLite yang mengabaikannya.
            $table->enum('status_bukti', ['lengkap', 'tidak_lengkap'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('spj_findings', function (Blueprint $table) {
            $table->dropColumn('status_bukti');
        });
    }
};
