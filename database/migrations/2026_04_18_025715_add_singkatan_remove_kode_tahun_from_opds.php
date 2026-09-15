<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opds', function (Blueprint $table) {
            $table->string('singkatan', 50)->nullable()->after('nama_opd');
        });

        Schema::table('opds', function (Blueprint $table) {
            $table->dropUnique(['kode_opd']);
            $table->dropColumn(['kode_opd', 'tahun_anggaran']);
        });
    }

    public function down(): void
    {
        Schema::table('opds', function (Blueprint $table) {
            $table->string('kode_opd', 50)->unique()->nullable();
            $table->year('tahun_anggaran')->nullable();
            $table->dropColumn('singkatan');
        });
    }
};
