<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        Schema::create('sp2d_funding_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sp2d_budget_code_id')->constrained('sp2d_budget_codes')->cascadeOnDelete();
            $table->foreignId('funding_source_id')->constrained('funding_sources')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->unique(['sp2d_budget_code_id', 'funding_source_id'], 'sp2d_funding_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sp2d_funding_allocations');
        Schema::dropIfExists('funding_sources');
    }
};
