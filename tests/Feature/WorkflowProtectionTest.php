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
