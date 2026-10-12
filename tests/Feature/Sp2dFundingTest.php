<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\Sp2d;
use App\Models\Spj;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class Sp2dFundingTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);
        $opd = Opd::create(['nama_opd' => 'Dinas']);
        $sp2d = Sp2d::create(['opd_id' => $opd->id, 'nomor_sp2d' => 'UJI', 'tahun' => 2026, 'triwulan' => 1, 'tanggal' => '2026-01-01', 'jumlah_total' => '100.30', 'created_by' => $user->id]);
        $code = $sp2d->budgetCodes()->create(['kode_belanja' => '5.1', 'uraian' => 'Belanja', 'jumlah' => '100.30']);

        return [$sp2d, $code];
    }

    public function test_split_funding_persists_exact_amounts_and_can_be_cleared(): void
    {
        [$sp2d, $code] = $this->fixture();
        $url = route('sp2d.funding.update', [$sp2d, $code]);
        $this->put($url, ['confirm_funding' => 1, 'allocations' => [['name' => 'Sumber A', 'amount' => '60.10'], ['name' => 'Sumber B', 'amount' => '40.20']]])->assertSessionHasNoErrors()->assertRedirect(route('sp2d.show', $sp2d));
        $this->assertDatabaseCount('funding_sources', 2);
        $this->assertDatabaseCount('sp2d_funding_allocations', 2);
        $this->assertSame(['60.10', '40.20'], $code->fundingAllocations()->orderBy('id')->get()->pluck('amount')->all());
        $this->get(route('sp2d.show', $sp2d))->assertOk()->assertSee('Sumber A')->assertSee('Sumber B');
        $this->get(route('sp2d.funding.edit', [$sp2d, $code]))->assertOk()->assertSee('Sumber A');
        $this->put($url, ['confirm_funding' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('sp2d_funding_allocations', 0);
        $this->get(route('sp2d.show', $sp2d))->assertSee('Belum ditentukan');
    }

    public function test_wrong_total_and_duplicates_roll_back_without_losing_previous_allocations(): void
    {
        [$sp2d, $code] = $this->fixture();
        $url = route('sp2d.funding.update', [$sp2d, $code]);
        $this->put($url, ['confirm_funding' => 1, 'allocations' => [['name' => 'Awal', 'amount' => '100.30']]])->assertSessionHasNoErrors();
        foreach ([
            [['name' => 'Berlebih', 'amount' => '101']],
            [['name' => 'Kurang', 'amount' => '100']],
            [['name' => 'Ganda', 'amount' => '60.10'], ['name' => 'Ganda', 'amount' => '40.20']],
            [['name' => 'Negatif', 'amount' => '-100.30']],
        ] as $rows) {
            $this->put($url, ['confirm_funding' => 1, 'allocations' => $rows])->assertSessionHasErrors();
            $this->assertDatabaseCount('funding_sources', 1);
            $this->assertSame('100.30', $code->fundingAllocations()->first()->amount);
        }
    }

    public function test_reviewed_sp2d_and_unauthorized_user_cannot_change_allocations(): void
    {
        [$sp2d, $code] = $this->fixture();
        Spj::create(['sp2d_id' => $sp2d->id, 'opd_id' => $sp2d->opd_id, 'nomor_spj' => 'SPJ', 'tanggal' => '2026-01-01', 'jumlah' => 100.30, 'status' => 'reviewed']);
        $url = route('sp2d.funding.update', [$sp2d, $code]);
        $data = ['confirm_funding' => 1, 'allocations' => [['name' => 'Dana', 'amount' => '100.30']]];
        $this->put($url, $data)->assertSessionHasErrors('allocations');
        $this->assertDatabaseCount('funding_sources', 0);
        $this->actingAs(User::factory()->create())->put($url, $data)->assertForbidden();
    }

    public function test_rincian_must_belong_to_sp2d_in_url(): void
    {
        [$sp2d, $code] = $this->fixture();
        $other = $sp2d->replicate();
        $other->nomor_sp2d = 'OTHER';
        $other->save();
        $this->get(route('sp2d.funding.edit', [$other, $code]))->assertNotFound();
        $this->put(route('sp2d.funding.update', [$other, $code]), ['confirm_funding' => 1])->assertNotFound();
    }

    public function test_existing_allocation_protects_nominal_from_becoming_inconsistent(): void
    {
        [$sp2d, $code] = $this->fixture();
        $this->put(route('sp2d.funding.update', [$sp2d, $code]), ['confirm_funding' => 1, 'allocations' => [['name' => 'Dana', 'amount' => '100.30']]])->assertSessionHasNoErrors();
        $this->expectException(ValidationException::class);
        $code->update(['jumlah' => 200]);
    }
}
