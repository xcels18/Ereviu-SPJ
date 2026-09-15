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
        Schema::table('spj', function (Blueprint $table) {
            $table->unsignedBigInteger('sp2d_budget_code_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spj', function (Blueprint $table) {
            $table->unsignedBigInteger('sp2d_budget_code_id')->nullable(false)->change();
        });
    }
};
