<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Tambahkan opsi status "belum_dibelanjakan" pada status_bukti akun
     * belanja. Berbeda dari "tidak_lengkap" (bukti seharusnya ada tapi belum
     * diupload), status ini untuk akun belanja yang dananya memang belum
     * dibelanjakan sehingga tidak ada bukti yang perlu/bisa diupload.
     *
     * Memakai schema builder agar perubahan enum juga dapat diuji di SQLite.
     */
    public function up(): void
    {
        Schema::table('spj_findings', function (Blueprint $table) {
            $table->enum('status_bukti', ['lengkap', 'tidak_lengkap', 'belum_dibelanjakan'])->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('spj_findings')->where('status_bukti', 'belum_dibelanjakan')->update(['status_bukti' => null]);
        Schema::table('spj_findings', function (Blueprint $table) {
            $table->enum('status_bukti', ['lengkap', 'tidak_lengkap'])->nullable()->change();
        });
    }
};
