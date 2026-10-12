<?php

use App\Support\RequiredDocuments;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // SQLite must be allowed to disable FK checks while rebuilding tables.
    public $withinTransaction = false;

    public function up(): void
    {
        $guides = DB::table('review_guides')->get();
        foreach ($guides as $guide) {
            RequiredDocuments::decode($guide->required_documents);
        }
        foreach ($guides as $guide) {
            DB::table('budget_accounts')->insertOrIgnore(['code' => $guide->kode_rekening, 'created_at' => now(), 'updated_at' => now()]);
        }
        // Alter the parent before creating children: SQLite table rebuilds can cascade deletes.
        Schema::table('review_guides', function (Blueprint $table) {
            $table->dropColumn('required_documents');
            $table->foreign('kode_rekening')->references('code')->on('budget_accounts')->restrictOnDelete();
        });
        Schema::create('review_guide_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_guide_id')->constrained('review_guides')->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->text('name');
            $table->unique(['review_guide_id', 'position']);
        });
        foreach ($guides as $guide) {
            DB::table('budget_accounts')->insertOrIgnore(['code' => $guide->kode_rekening, 'created_at' => now(), 'updated_at' => now()]);
            foreach (RequiredDocuments::decode($guide->required_documents) as $position => $name) {
                DB::table('review_guide_requirements')->insert(['review_guide_id' => $guide->id, 'position' => $position, 'name' => $name]);
            }
        }
    }

    public function down(): void
    {
        $documents = [];
        foreach (DB::table('review_guides')->select('id')->cursor() as $guide) {
            $documents[$guide->id] = DB::table('review_guide_requirements')->where('review_guide_id', $guide->id)->orderBy('position')->pluck('name')->all();
        }
        Schema::dropIfExists('review_guide_requirements');
        Schema::table('review_guides', function (Blueprint $table) {
            $table->dropForeign(['kode_rekening']);
            $table->text('required_documents')->nullable();
        });
        foreach ($documents as $id => $names) {
            DB::table('review_guides')->where('id', $id)->update(['required_documents' => json_encode($names, JSON_UNESCAPED_UNICODE)]);
        }
    }
};
