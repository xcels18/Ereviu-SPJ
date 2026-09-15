<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditor_opd_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('opd_id')->constrained('opds')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'opd_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditor_opd_assignments');
    }
};