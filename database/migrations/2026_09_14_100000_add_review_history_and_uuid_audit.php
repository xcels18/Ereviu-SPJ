<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', fn (Blueprint $t) => $t->string('subject_id', 100)->nullable()->change());
        Schema::table('spj', function (Blueprint $t) {
            $t->unsignedInteger('review_version')->default(0);
            $t->string('review_mode', 20)->nullable();
        });
        Schema::create('pending_file_deletions', function (Blueprint $t) {
            $t->id();
            $t->string('disk', 20);
            $t->text('path');
            $t->timestamps();
        });
        Schema::create('spj_review_history', function (Blueprint $t) {
            $t->id();
            $t->foreignId('spj_id')->constrained('spj')->restrictOnDelete();
            $t->unsignedInteger('version');
            $t->json('snapshot');
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('reason')->nullable();
            $t->timestamps();
            $t->unique(['spj_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spj_review_history');
        Schema::dropIfExists('pending_file_deletions');
        Schema::table('spj', fn (Blueprint $t) => $t->dropColumn(['review_version', 'review_mode']));
        // Keep subject_id string: conversion back to integer would lose UUID history.
    }
};
