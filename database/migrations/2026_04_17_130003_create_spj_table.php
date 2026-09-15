<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spj', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sp2d_id')->constrained('sp2d')->onDelete('cascade');
            $table->foreignId('opd_id')->constrained('opds')->onDelete('cascade');
            $table->foreignId('sp2d_budget_code_id')->constrained('sp2d_budget_codes')->onDelete('cascade');
            $table->string('nomor_spj', 100);
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2);
            $table->enum('status', ['draft', 'submitted', 'in_review', 'reviewed'])->default('draft');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spj');
    }
};