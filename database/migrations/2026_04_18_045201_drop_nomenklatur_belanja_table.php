<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('nomenklatur_belanja');
    }

    public function down(): void
    {
        //
    }
};
