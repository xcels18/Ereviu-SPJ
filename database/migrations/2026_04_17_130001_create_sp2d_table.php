<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sp2d', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained('opds')->onDelete('cascade');
            $table->string('nomor_sp2d', 100)->unique();
            $table->year('tahun');
            $table->tinyInteger('triwulan'); // 1, 2, 3, 4
            $table->date('tanggal');
            $table->decimal('jumlah_total', 15, 2);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sp2d');
    }
};