<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sp2d_budget_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sp2d_id')->constrained('sp2d')->onDelete('cascade');
            $table->string('kode_belanja', 50);
            $table->string('uraian');
            $table->decimal('jumlah', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sp2d_budget_codes');
    }
};