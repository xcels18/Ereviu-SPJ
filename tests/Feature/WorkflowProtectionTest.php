<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\Sp2d;
use App\Models\Spj;
use App\Models\SpjDocument;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkflowProtectionTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo(['manage_sp2d', 'review_spj']);
        $user->assignRole('admin');
        $this->actingAs($user);
        $opd = Opd::create(['nama_opd' => 'Uji']);
        $data = ['opd_id' => $opd->id, 'nomor_sp2d' => 'SP2D/TU/UJI', 'tahun' => 2026, 'triwulan' => 3, 'tanggal' => '2026-09-01', 'jumlah_total' => 1000, 'created_by' => $user->id];
        $sp2d = Sp2d::create($data);
        $code = $sp2d->budgetCodes()->create(['kode_belanja' => '5.1', 'uraian' => 'Awal', 'jumlah' => 1000]);

        return [$sp2d, $code, $data];
    }

    private function spj(Sp2d $sp2d): Spj
    {
        return Spj::create(['sp2d_id' => $sp2d->id, 'opd_id' => $sp2d->opd_id, 'nomor_spj' => 'UJI', 'tanggal' => '2026-09-01', 'jumlah' => 1000, 'status' => 'submitted']);
    }

    public function test_spending_question_filters_sum_only_matching_accounts(): void
    {
        [$sp2d] = $this->fixture();
        $sp2d->update(['tanggal'=>'2026-02-12']);
        $sp2d->budgetCodes()->create(['kode_belanja'=>'5.2', 'uraian'=>'Belanja Perjalanan Dinas Dalam Negeri', 'jumlah'=>250]);
        $sp2d->budgetCodes()->create(['kode_belanja'=>'5.2', 'uraian'=>'Belanja Perjalanan Dinas Dalam Negeri', 'jumlah'=>125]);
        $this->get(route('spending-analysis.index', ['tahun'=>2026,'triwulan'=>1,'belanja'=>'perjalanan dinas']))->assertOk()
            ->assertViewHas('totals', fn ($t) => (float)$t->amount === 375.0 && (int)$t->documents === 1)
            ->assertViewHas('ranking', fn ($rows) => (float)$rows->first()->amount === 375.0)
            ->assertViewHas('months', fn ($months) => $months->count() === 3)
            ->assertViewHas('spendingNames', fn ($names) => $names->contains('Belanja Perjalanan Dinas Dalam Negeri'));
        $this->get(route('spending-analysis.index', ['tahun'=>2026,'triwulan'=>2,'belanja'=>'perjalanan dinas']))->assertOk()
            ->assertViewHas('totals', fn ($t) => (float)$t->amount === 0.0)
            ->assertViewHas('spendingNames', fn ($names) => $names->isEmpty());
        $this->get(route('spending-analysis.index', ['tahun'=>2026,'kode'=>'5.1']))->assertOk()
            ->assertViewHas('totals', fn ($t) => (float)$t->amount === 1000.0);
    }

    public function test_spending_modal_returns_matching_spj_and_enforces_scope(): void
    {
        [$sp2d] = $this->fixture();
        $this->spj($sp2d);
        $params = ['tahun'=>2026, 'kode'=>'5.1', 'detail'=>1];
        $this->getJson(route('spending-analysis.index', $params))->assertOk()->assertJsonPath('total', 1)
            ->assertJsonPath('rows.0.number', $sp2d->nomor_sp2d)->assertJsonPath('rows.0.status', 'submitted');
        $this->getJson(route('spending-analysis.index', ['tahun'=>2026,'bulan'=>9,'detail'=>1]))->assertOk()->assertJsonPath('total', 1);
        $this->getJson(route('spending-analysis.index', ['tahun'=>2026,'detail'=>1,'exclude_codes'=>['5.1']]))->assertOk()->assertJsonPath('total', 0);
        $other = User::factory()->create();
        $other->assignRole('irban');
        $this->actingAs($other)->getJson(route('spending-analysis.index', $params))->assertOk()->assertJsonPath('total', 0);
        $this->getJson(route('spending-analysis.index', $params + ['opd_id'=>$sp2d->opd_id]))->assertForbidden();
    }

    public function test_spending_advanced_filters_and_validation(): void
    {
        $this->fixture();
        $this->get(route('spending-analysis.index', ['tahun'=>2026,'bulan'=>9,'nilai_max'=>1000,'nomor_sp2d'=>'TU']))->assertOk()
            ->assertViewHas('totals', fn ($t) => (float)$t->amount === 1000.0);
        $this->get(route('spending-analysis.index', ['tahun'=>2026,'tanggal_dari'=>'2026-10-01','nilai_min'=>500]))->assertOk()
            ->assertViewHas('totals', fn ($t) => (float)$t->amount === 0.0);
        $this->getJson(route('spending-analysis.index', ['nilai_min'=>500,'nilai_max'=>100]))->assertUnprocessable();
        $this->getJson(route('spending-analysis.index', ['tanggal_dari'=>'2026-10-01','tanggal_sampai'=>'2026-09-01']))->assertUnprocessable();
    }

    public function test_spending_analysis_totals_and_scope(): void
    {
        [$sp2d] = $this->fixture();
        $sp2d->budgetCodes()->create(['kode_belanja'=>'5.1', 'uraian'=>'Tambahan', 'jumlah'=>100]);
        $this->get(route('spending-analysis.index', ['tahun'=>2026]))->assertOk()
            ->assertViewHas('totals', fn ($totals) => (int)$totals->documents === 1 && (float)$totals->amount === 1100.0)
            ->assertViewHas('months', fn ($months) => $months->count() === 12 && $months[8]['amount'] === 1100.0)
            ->assertViewHas('accounts', fn ($accounts) => (int)$accounts->first()->entries === 2);
        $this->get(route('spending-analysis.index', ['tahun'=>2025]))->assertOk()->assertSee('Tidak ada rincian belanja sesuai pilihan');
        $other = User::factory()->create();
        $other->assignRole('irban');
        $this->actingAs($other)->get(route('spending-analysis.index', ['opd_id'=>$sp2d->opd_id]))->assertForbidden();
        $this->get(route('spending-analysis.index'))->assertOk()->assertViewHas('totals', fn ($totals) => (int)$totals->documents === 0);
    }

    public function test_report_separates_unspent_accounts_and_renders_both_views(): void
    {
        [$sp2d, $code] = $this->fixture();
        $spj = $this->spj($sp2d);
        $spj->findings()->create(['sp2d_budget_code_id' => $code->id, 'finding_type' => 'note', 'status_bukti' => 'belum_dibelanjakan', 'description' => '', 'created_by' => auth()->id()]);
        $this->get(route('reports.index'))->assertOk()
            ->assertSee('Status akun belanja')
            ->assertViewHas('summary', fn ($summary) => $summary->akun_belum_dibelanjakan === 1 && $summary->total_akun === 1 && $summary->akun_lengkap - $summary->akun_belum_dibelanjakan === 0);
        $this->get(route('reports.index', ['view' => 'bulanan']))->assertOk()->assertSee('Tuntas');
        $this->get(route('reports.index', ['search' => 'Tidak ditemukan']))->assertOk()->assertSee('Tidak ada OPD sesuai filter');
    }

    public function test_report_irban_filter_limits_opds_and_rejects_outside_selection(): void
    {
        [$sp2d] = $this->fixture();
        $irban = User::factory()->create();
        $irban->assignRole('irban');
        $irban->auditorOpdAssignments()->create(['opd_id' => $sp2d->opd_id]);
        $other = Opd::create(['nama_opd' => 'Di luar wilayah']);

        $this->get(route('reports.cetak', ['irban_id' => $irban->id, 'opd_id' => $sp2d->opd_id]))
            ->assertOk()
            ->assertViewHas('opds', fn ($opds) => $opds->modelKeys() === [$sp2d->opd_id])
            ->assertViewHas('selectedOpd', fn ($opd) => $opd->id === $sp2d->opd_id);
        $this->get(route('reports.cetak', ['irban_id' => $irban->id, 'opd_id' => $other->id, 'export' => 'excel']))
            ->assertOk()->assertViewHas('selectedOpd', null)->assertViewHas('groups', []);

        $outsider = User::factory()->create();
        $outsider->assignRole('irban');
        $this->actingAs($outsider)->get(route('reports.cetak', ['irban_id' => $irban->id]))->assertForbidden();
    }

    public function test_edit_preserves_ids_and_rejects_used_account_deletion_atomically(): void
    {
        [$sp2d, $code, $data] = $this->fixture();
        $spj = $this->spj($sp2d);
        $this->putJson(route('sp2d.update', $sp2d), $data + ['budget_codes' => [['id' => $code->id, 'kode_belanja' => '5.1', 'uraian' => 'Koreksi', 'jumlah' => 1000]]])->assertRedirect();
        $this->assertSame('Koreksi', $code->fresh()->uraian);
        $data['nomor_sp2d'] = 'CHANGED';
        $this->putJson(route('sp2d.update', $sp2d), $data + ['budget_codes' => []])->assertUnprocessable();
        $this->assertSame('SP2D/TU/UJI', $sp2d->fresh()->nomor_sp2d);
        $this->assertNotNull($code->fresh());
        $this->deleteJson(route('sp2d.destroy', $sp2d))->assertUnprocessable();
        $this->assertNotNull($spj->fresh());
    }

    public function test_foreign_account_id_and_opd_transfer_are_rejected(): void
    {
        [$sp2d, $code, $data] = $this->fixture();
        $this->putJson(route('sp2d.update', $sp2d), $data + ['budget_codes' => [['id' => 999999, 'kode_belanja' => 'X', 'jumlah' => 1]]])->assertUnprocessable();
        $this->spj($sp2d);
        $data['opd_id'] = Opd::create(['nama_opd' => 'Lain'])->id;
        $this->putJson(route('sp2d.update', $sp2d), $data)->assertUnprocessable();
        $this->assertNotNull($code->fresh());
    }

    public function test_completion_requires_all_assessments_and_failure_does_not_write_findings(): void
    {
        [$sp2d, $code] = $this->fixture();
        $other = $sp2d->budgetCodes()->create(['kode_belanja' => '5.2', 'uraian' => 'Kedua', 'jumlah' => 0]);
        $spj = $this->spj($sp2d);
        $payload = ['review_version' => 0, 'status' => 'reviewed', 'mode_gabungan' => '0', 'status_bukti' => [$code->id => 'lengkap']];
        $this->postJson(route('spj.review.store', $spj), $payload)->assertUnprocessable()->assertJsonValidationErrors("status_bukti.{$other->id}");
        $this->assertDatabaseCount('spj_findings', 0);
        $this->assertSame('submitted', $spj->fresh()->status);
        $payload['status_bukti'][$other->id] = 'tidak_lengkap';
        $this->postJson(route('spj.review.store', $spj), $payload)->assertRedirect();
        $this->assertSame('reviewed', $spj->fresh()->status);
        $this->assertNotNull($spj->fresh()->reviewed_at);
        $payload['status'] = 'in_review';
        $payload['review_version'] = 1;
        $payload['review_reason'] = 'Koreksi pemeriksaan';
        $this->postJson(route('spj.review.store', $spj), $payload)->assertRedirect();
        $this->assertNull($spj->fresh()->reviewed_at);
    }

    public function test_mode_switch_archives_previous_scope_and_rejects_stale_form(): void
    {
        [$sp2d, $code] = $this->fixture();
        $spj = $this->spj($sp2d);
        $this->postJson(route('spj.review.store', $spj), ['review_version' => 0, 'status' => 'in_review', 'mode_gabungan' => '0', 'status_bukti' => [$code->id => 'tidak_lengkap']])->assertRedirect();
        $this->postJson(route('spj.review.store', $spj), ['review_version' => 1, 'status' => 'reviewed', 'mode_gabungan' => '1', 'status_bukti_gabungan' => 'lengkap'])->assertRedirect();
        $this->assertSame('combined', $spj->fresh()->review_mode);
        $this->assertDatabaseMissing('spj_findings', ['spj_id' => $spj->id, 'sp2d_budget_code_id' => $code->id]);
        $this->assertDatabaseCount('spj_review_history', 2);
        $snapshot = json_decode(DB::table('spj_review_history')->where('version', 1)->value('snapshot'), true);
        $this->assertSame('tidak_lengkap', $snapshot['findings'][0]['status_bukti']);
        $this->postJson(route('spj.review.store', $spj), ['review_version' => 1, 'status' => 'in_review'])->assertUnprocessable()->assertJsonValidationErrors('review_version');
    }

    public function test_failed_upload_rolls_back_header_and_cleans_written_files(): void
    {
        [$sp2d] = $this->fixture();
        Storage::fake('local');
        SpjDocument::creating(function () {
            throw new \RuntimeException('Simulated database failure');
        });
        try {
            $this->withoutExceptionHandling()->post(route('spj.store'), ['sp2d_id' => $sp2d->id, 'sp2d_scan' => [UploadedFile::fake()->create('scan.pdf', 10, 'application/pdf')]]);
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated database failure', $e->getMessage());
            $this->assertDatabaseCount('spj', 0);
            $this->assertSame([], Storage::disk('local')->allFiles());
        } finally {
            SpjDocument::flushEventListeners();
        }
    }
}
