<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpdDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_searches_unit_code_and_displays_empty_results(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('manage_opd');
        Opd::create(['nama_opd' => 'Dinas Uji', 'singkatan' => 'DU', 'kode_unit' => 'UNIT-123']);
        Opd::create(['nama_opd' => 'Dinas Lain']);
        $this->actingAs($user)->get(route('opd.index', ['search' => 'UNIT-123']))
            ->assertOk()->assertSee('Dinas Uji')->assertDontSee('Dinas Lain')
            ->assertViewHas('totalOpd', 2)->assertViewHas('withSp2d', 0);
        $this->get(route('opd.index', ['search' => 'tidak-ada']))->assertOk()->assertSee('Perangkat daerah tidak ditemukan');
    }
}
