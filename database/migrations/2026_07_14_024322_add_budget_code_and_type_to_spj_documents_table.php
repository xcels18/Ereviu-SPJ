<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('spj_documents', function (Blueprint $table) {
            $table->foreignId('sp2d_budget_code_id')->nullable()->after('spj_id')
                ->constrained('sp2d_budget_codes')->nullOnDelete();
            $table->string('document_type')->default('bukti_belanja')->after('sp2d_budget_code_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spj_documents', function (Blueprint $table) {
            $table->dropForeign(['sp2d_budget_code_id']);
            $table->dropColumn(['sp2d_budget_code_id', 'document_type']);
        });
    }
};
