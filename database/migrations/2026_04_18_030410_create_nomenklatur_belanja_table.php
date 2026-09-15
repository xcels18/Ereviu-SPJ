<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomenklatur_belanja', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->index();
            $table->string('nama_akun_belanja');
            $table->string('dasar_kepmendagri', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['kode', 'dasar_kepmendagri']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomenklatur_belanja');
    }
};
