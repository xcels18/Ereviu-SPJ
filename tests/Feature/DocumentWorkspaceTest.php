<?php

namespace Tests\Feature;

use App\Models\{User, Opd, Sp2d};
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sp2d_search_and_summary_use_the_same_filtered_dataset(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $opd = Opd::create(['nama_opd' => 'OPD Pertama']);
        $other = Opd::create(['nama_opd' => 'OPD Kedua']);
        foreach ([[$opd, 'SP2D/TEST/01', 1500], [$other, 'SP2D/OTHER/02', 3000]] as [$unit, $number, $amount]) {
            Sp2d::create(['opd_id' => $unit->id, 'nomor_sp2d' => $number, 'tahun' => 2026, 'triwulan' => 3, 'tanggal' => '2026-09-05', 'jumlah_total' => $amount, 'created_by' => $user->id]);
        }
        $this->actingAs($user)->get(route('sp2d.index', ['search' => 'TEST', 'tahun' => 2026]))
            ->assertOk()->assertViewHas('totalCount', 1)->assertViewHas('totalOpd', 1)
            ->assertViewHas('totalNilai', fn ($value) => (float) $value === 1500.0)
            ->assertSee('SP2D/TEST/01')->assertDontSee('SP2D/OTHER/02');
        $this->get(route('sp2d.index', ['search' => 'NO-MATCH']))->assertOk()->assertSee('Belum ada SP2D dalam daftar ini');
    }

    public function test_spj_directory_respects_assignment_and_renders_selected_opd(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('irban');
        $assigned = Opd::create(['nama_opd' => 'OPD Ditugaskan']);
        Opd::create(['nama_opd' => 'OPD Rahasia']);
        \App\Models\AuditorOpdAssignment::create(['user_id' => $user->id, 'opd_id' => $assigned->id]);
        $this->actingAs($user)->get(route('spj.upload'))->assertOk()->assertSee('OPD Ditugaskan')->assertDontSee('OPD Rahasia');
        $this->get(route('spj.upload', ['opd_id' => $assigned->id]))->assertOk()->assertSee('OPD Ditugaskan')->assertDontSee('OPD Rahasia');
    }
}
