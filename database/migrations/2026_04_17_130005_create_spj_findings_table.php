<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spj_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spj_id')->constrained('spj')->onDelete('cascade');
            $table->enum('finding_type', ['minor', 'major', 'note']);
            $table->text('description');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spj_findings');
    }
};