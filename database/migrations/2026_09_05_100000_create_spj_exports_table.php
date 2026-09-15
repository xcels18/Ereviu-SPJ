<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spj_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opd_id')->constrained('opds')->cascadeOnDelete();
            $table->string('opd_name');
            $table->string('mode', 20);
            $table->string('compression', 20);
            $table->string('status', 20)->default('queued')->index();
            $table->json('selection');
            $table->unsignedBigInteger('total_files')->default(0);
            $table->unsignedBigInteger('processed_files')->default(0);
            $table->unsignedBigInteger('total_bytes')->default(0);
            $table->unsignedBigInteger('processed_bytes')->default(0);
            $table->json('parts')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spj_exports');
    }
};
