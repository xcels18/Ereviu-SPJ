<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_affairs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        foreach ([
            ['budget_programs', 'budget_affair_id', 'budget_affairs'],
            ['budget_activities', 'budget_program_id', 'budget_programs'],
            ['budget_subactivities', 'budget_activity_id', 'budget_activities'],
        ] as [$name, $parent, $parentTable]) {
            Schema::create($name, function (Blueprint $table) use ($parent, $parentTable) {
                $table->id();
                $table->foreignId($parent)->constrained($parentTable)->restrictOnDelete();
                $table->string('name');
                $table->unique([$parent, 'name']);
                $table->timestamps();
            });
        }
        Schema::table('sp2d_budget_codes', function (Blueprint $table) {
            // Legacy and manually entered lines may not have source hierarchy information.
            $table->foreignId('budget_subactivity_id')->nullable()->constrained('budget_subactivities')->restrictOnDelete();
        });
        Schema::create('sp2d_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sp2d_budget_code_id')->unique()->constrained('sp2d_budget_codes')->cascadeOnDelete();
            $table->string('source_file');
            $table->unsignedInteger('row_number');
            // Ordered arrays preserve repeated Excel headings such as URAIAN.
            $table->json('headers');
            $table->json('values');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sp2d_import_rows');
        Schema::table('sp2d_budget_codes', fn (Blueprint $table) => $table->dropConstrainedForeignId('budget_subactivity_id'));
        foreach (['budget_subactivities', 'budget_activities', 'budget_programs', 'budget_affairs'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
