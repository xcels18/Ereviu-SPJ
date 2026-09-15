<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $relations = [
        ['sp2d', 'created_by', 'users'],
        ['sp2d', 'opd_id', 'opds'],
        ['spj_documents', 'uploaded_by', 'users'],
        ['spj_findings', 'created_by', 'users'],
        ['spj_documents', 'sp2d_budget_code_id', 'sp2d_budget_codes'],
        ['spj_findings', 'sp2d_budget_code_id', 'sp2d_budget_codes'],
        ['spj', 'sp2d_budget_code_id', 'sp2d_budget_codes'],
    ];

    public function up(): void
    {
        if (DB::table('spj')->select('sp2d_id')->groupBy('sp2d_id')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Terdapat beberapa SPJ per SP2D. Migrasi dihentikan tanpa menggabungkan data.');
        }
        if (! Schema::hasIndex('spj', 'spj_one_per_sp2d')) {
            Schema::table('spj', fn (Blueprint $table) => $table->unique('sp2d_id', 'spj_one_per_sp2d'));
        }
        foreach ($this->relations as [$name, $column, $parent]) {
            $foreign = collect(Schema::getForeignKeys($name))->first(fn ($key) => $key['columns'] === [$column]);
            if ($foreign && strtolower($foreign['on_delete']) === 'restrict') {
                continue;
            }
            Schema::table($name, function (Blueprint $table) use ($column, $parent, $foreign) {
                if ($foreign) {
                    $table->dropForeign([$column]);
                }
                $table->foreign($column)->references('id')->on($parent)->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->relations as [$name, $column, $parent]) {
            Schema::table($name, function (Blueprint $table) use ($name, $column, $parent) {
                $table->dropForeign([$column]);
                $table->foreign($column)->references('id')->on($parent)
                    ->onDelete($column === 'sp2d_budget_code_id' && $name !== 'spj' ? 'set null' : 'cascade');
            });
        }
        Schema::table('spj', fn (Blueprint $table) => $table->dropUnique('spj_one_per_sp2d'));
    }
};
