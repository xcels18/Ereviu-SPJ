<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spj_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spj_id')->constrained('spj')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 50);
            $table->bigInteger('size');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('uploaded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spj_documents');
    }
};