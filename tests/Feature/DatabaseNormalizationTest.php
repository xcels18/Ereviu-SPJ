<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\ReviewGuide;
use App\Models\Sp2d;
use App\Models\Spj;
use App\Models\SpjDocument;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseNormalizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // These tests exercise DDL with real data, outside a wrapping transaction.
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $this->beforeApplicationDestroyed(function () {
            RefreshDatabaseState::$migrated = false;
        });
    }

    private function fixture(): array
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $opd = Opd::create(['nama_opd' => 'OPD']);
        $sp2d = Sp2d::create(['opd_id' => $opd->id, 'nomor_sp2d' => 'NORMAL', 'tanggal' => '2026-01-02', 'jumlah_total' => '0.30', 'created_by' => $user->id]);
        $code = $sp2d->budgetCodes()->create(['kode_belanja' => '5.1', 'uraian' => 'Kertas', 'jumlah' => '0.10']);
        $sp2d->budgetCodes()->create(['kode_belanja' => '5.1', 'uraian' => 'Tinta', 'jumlah' => '0.20']);
        $spj = Spj::create(['sp2d_id' => $sp2d->id, 'opd_id' => $opd->id, 'nomor_spj' => 'SPJ-NORMAL', 'tanggal' => '2026-01-03', 'jumlah' => '0.30', 'status' => 'submitted']);

        return [$user, $opd, $sp2d, $code, $spj];
    }

    public function test_single_sources_and_derived_period_are_used_by_reports(): void
    {
        [$user, $opd, $sp2d, $code, $spj] = $this->fixture();
        $this->assertFalse(Schema::hasColumn('users', 'role'));
        $this->assertFalse(Schema::hasColumn('spj', 'opd_id'));
        $this->assertFalse(Schema::hasColumn('sp2d', 'tahun'));
        $this->assertFalse(Schema::hasColumn('sp2d_budget_codes', 'kode_belanja'));
        $this->assertSame($opd->id, Spj::with('opd')->find($spj->id)->opd->id);
        $this->assertSame($opd->id, $spj->fresh()->opd_id);
        $this->assertDatabaseCount('budget_accounts', 1);
        $this->assertDatabaseCount('sp2d_budget_codes', 2);
        $this->assertSame('5.1', $code->fresh()->toArray()['kode_belanja']);
        $sp2d->update(['tanggal' => '2027-10-12']);
        $this->assertSame(2027, $sp2d->fresh()->tahun);
        $this->assertSame(4, $sp2d->fresh()->triwulan);
        $this->actingAs($user)->get(route('sp2d.index', ['tahun' => 2027]))->assertOk()->assertSee('NORMAL');
        $this->get(route('spending-analysis.index', ['tahun' => 2027]))->assertOk()->assertViewHas('totals', fn ($total) => number_format((float) $total->amount, 2, '.', '') === '0.30');
    }

    public function test_opd_scope_still_hides_spj_owned_by_another_opd(): void
    {
        $this->fixture();
        $other = Opd::create(['nama_opd' => 'Lain']);
        $user = User::factory()->create(['opd_id' => $other->id]);
        $user->assignRole('opd_admin');
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertViewHas('totalSpjUploaded', 0);
        $this->get(route('reports.spj'))->assertOk()->assertViewHas('spjs', fn ($rows) => $rows->total() === 0);
        $this->get(route('reports.findings'))->assertOk();
    }

    public function test_guide_requirements_are_individual_rows_and_keep_order(): void
    {
        $guide = ReviewGuide::create(['kode_rekening' => '5.2', 'nama_belanja' => 'Panduan', 'required_documents' => json_encode(json_encode(['Kontrak', 'Bukti, termasuk lampiran', 'Kontrak'])), 'is_active' => true]);
        $this->assertFalse(Schema::hasColumn('review_guides', 'required_documents'));
        $this->assertDatabaseCount('review_guide_requirements', 3);
        $this->assertSame(['Kontrak', 'Bukti, termasuk lampiran', 'Kontrak'], $guide->fresh()->required_documents);
        $guide->update(['required_documents' => ['Laporan']]);
        $this->assertDatabaseCount('review_guide_requirements', 1);
        $this->assertSame(['Laporan'], $guide->fresh()->required_documents);
        $guide->delete();
        $this->assertDatabaseCount('review_guide_requirements', 0);
        $this->assertDatabaseCount('budget_accounts', 1);
    }

    public function test_migration_backfills_legacy_data_without_merging_transaction_rows(): void
    {
        [$user, $opd, $sp2d, $code, $spj] = $this->fixture();
        $guide = ReviewGuide::create(['kode_rekening' => '5.1', 'nama_belanja' => 'Panduan', 'required_documents' => ['A', 'B'], 'is_active' => true]);
        $core = require database_path('migrations/2026_10_12_100000_normalize_core_relations.php');
        $requirements = require database_path('migrations/2026_10_12_110000_normalize_review_requirements.php');
        $document = SpjDocument::create(['spj_id' => $spj->id, 'sp2d_budget_code_id' => $code->id, 'document_type' => 'bukti_belanja', 'file_name' => 'proof.pdf', 'file_path' => 'proof.pdf', 'file_type' => 'application/pdf', 'size' => 123, 'uploaded_by' => $user->id]);
        $requirements->down();
        $core->down();
        $this->assertDatabaseHas('sp2d_budget_codes', ['id' => $code->id, 'kode_belanja' => '5.1']);
        $this->assertDatabaseHas('spj', ['id' => $spj->id, 'opd_id' => $opd->id]);
        $core->up();
        $requirements->up();
        $this->assertSame('5.1', $code->fresh()->kode_belanja);
        $this->assertDatabaseCount('sp2d_budget_codes', 2);
        $this->assertDatabaseCount('budget_accounts', 1);
        $this->assertSame(['A', 'B'], $guide->fresh()->required_documents);
        $this->assertTrue($user->fresh()->hasRole('admin'));
        $this->assertDatabaseHas('spj', ['id' => $spj->id, 'sp2d_id' => $sp2d->id]);
        $this->assertDatabaseHas('spj_documents', ['id' => $document->id, 'spj_id' => $spj->id, 'sp2d_budget_code_id' => $code->id]);
        $this->assertSame('0.30', number_format((float) $sp2d->fresh()->jumlah_total, 2, '.', ''));
    }

    public function test_conflicting_legacy_period_stops_before_any_schema_change(): void
    {
        [, , $sp2d] = $this->fixture();
        $core = require database_path('migrations/2026_10_12_100000_normalize_core_relations.php');
        $requirements = require database_path('migrations/2026_10_12_110000_normalize_review_requirements.php');
        $requirements->down();
        $core->down();
        DB::table('sp2d')->where('id', $sp2d->id)->update(['tahun' => 2025]);
        try {
            $core->up();
            $this->fail('Conflicting period must be rejected');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Periode SP2D', $e->getMessage());
            $this->assertTrue(Schema::hasColumn('users', 'role'));
            $this->assertFalse(Schema::hasTable('budget_accounts'));
            DB::table('sp2d')->where('id', $sp2d->id)->update(['tahun' => 2026]);
            $core->up();
            $requirements->up();
        }
    }
}
