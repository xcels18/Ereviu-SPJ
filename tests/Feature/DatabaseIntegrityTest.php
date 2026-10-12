<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\Sp2d;
use App\Models\Spj;
use App\Models\SpjDocument;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $u = User::factory()->create();
        $o = Opd::create(['nama_opd' => 'Uji']);
        $p = Sp2d::create(['opd_id' => $o->id, 'nomor_sp2d' => 'UJI', 'tahun' => 2026, 'triwulan' => 3, 'tanggal' => '2026-09-01', 'jumlah_total' => 1, 'created_by' => $u->id]);
        $s = Spj::create(['sp2d_id' => $p->id, 'opd_id' => $o->id, 'nomor_spj' => 'UJI', 'tanggal' => '2026-09-01', 'jumlah' => 1, 'status' => 'submitted']);

        return [$u, $o, $p, $s];
    }

    public function test_database_rejects_duplicate_spj(): void
    {
        [,,, $s] = $this->fixture();
        $this->expectException(QueryException::class);
        DB::table('spj')->insert(collect($s->getAttributes())->except('id')->all());
    }

    public function test_direct_user_deletion_cannot_cascade_archives(): void
    {
        [$u] = $this->fixture();
        try {
            DB::table('users')->where('id', $u->id)->delete();
            $this->fail('Delete should be rejected');
        } catch (QueryException $e) {
            $this->assertDatabaseCount('sp2d', 1);
            $this->assertDatabaseCount('spj', 1);
        }
    }

    public function test_document_cannot_reference_account_of_other_sp2d(): void
    {
        [$u,$o,$p,$s] = $this->fixture();
        $other = $p->replicate();
        $other->nomor_sp2d = 'OTHER';
        $other->save();
        $code = $other->budgetCodes()->create(['kode_belanja' => '5.1', 'uraian' => 'Uji', 'jumlah' => 1]);
        $this->expectException(ValidationException::class);
        SpjDocument::create(['spj_id' => $s->id, 'sp2d_budget_code_id' => $code->id, 'document_type' => 'bukti_belanja', 'file_name' => 'x.pdf', 'file_path' => 'x.pdf', 'file_type' => 'application/pdf', 'size' => 1, 'uploaded_by' => $u->id]);
    }

    public function test_role_display_uses_single_permission_source(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $u = User::factory()->create();
        $u->assignRole('irban');
        $this->assertSame('irban', $u->primary_role);
        $this->assertSame(1, User::role('irban')->count());
        $this->assertSame(0, User::role('admin')->count());
    }
}
