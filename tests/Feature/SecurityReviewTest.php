<?php

namespace Tests\Feature;

use App\Models\{User, Opd, Sp2d, Spj, AuditorOpdAssignment};
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviewer_cannot_review_outside_assigned_opd(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('irban');
        $opd = Opd::create(['nama_opd' => 'OPD Uji']);
        $sp2d = Sp2d::create(['opd_id' => $opd->id, 'nomor_sp2d' => 'SP2D/UP/TEST', 'tahun' => 2026, 'triwulan' => 3, 'tanggal' => '2026-09-01', 'jumlah_total' => 1000, 'created_by' => $user->id]);
        $spj = Spj::create(['sp2d_id' => $sp2d->id, 'opd_id' => $opd->id, 'nomor_spj' => 'TEST', 'tanggal' => '2026-09-01', 'jumlah' => 1000, 'status' => 'submitted']);
        $this->actingAs($user)->post(route('spj.review.store', $spj), ['review_version' => 0, 'status' => 'reviewed'])->assertForbidden();
        $this->assertSame('submitted', $spj->fresh()->status);
        AuditorOpdAssignment::create(['user_id' => $user->id, 'opd_id' => $opd->id]);
        $this->post(route('spj.review.store', $spj), ['review_version' => 0, 'status' => 'reviewed', 'status_bukti_gabungan' => 'belum_dibelanjakan'])->assertRedirect();
        $this->assertSame('reviewed', $spj->fresh()->status);
    }

    public function test_upload_only_user_cannot_submit_review_fields(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('operator_irban');
        $this->actingAs($user)->post(route('spj.store'), ['status_bukti_gabungan' => 'belum_dibelanjakan'])->assertForbidden();
        $this->assertDatabaseCount('spj_findings', 0);
    }

    public function test_mfa_challenge_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('mfa.challenge.store'), ['code' => '000000'])->assertRedirect();
        }
        $this->post(route('mfa.challenge.store'), ['code' => '000000'])->assertStatus(429);
    }
}
