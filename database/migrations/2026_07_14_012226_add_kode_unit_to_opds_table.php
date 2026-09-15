<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('opds', function (Blueprint $table) {
            $table->string('kode_unit', 50)->nullable()->unique()->after('nama_opd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opds', function (Blueprint $table) {
            $table->dropColumn('kode_unit');
        });
    }
};
