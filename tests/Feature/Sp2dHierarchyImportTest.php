<?php

namespace Tests\Feature;

use App\Exports\Sp2dTemplateExport;
use App\Models\BudgetAffair;
use App\Models\BudgetProgram;
use App\Models\Opd;
use App\Models\Sp2d;
use App\Models\Sp2dBudgetCode;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class Sp2dHierarchyImportTest extends TestCase
{
    use RefreshDatabase;

    private function login(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);
    }

    private function upload(array $headers, array $rows)
    {
        // Exercise the real XLSX writer/reader, including two columns named URAIAN.
        $book = new Spreadsheet;
        $book->getActiveSheet()->fromArray([$headers, ...$rows]);
        $path = tempnam(sys_get_temp_dir(), 'hierarchy-');
        try {
            (new Xlsx($book))->save($path);

            return $this->post(route('sp2d.import'), [
                'file' => new UploadedFile($path, 'sp2d.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
            ]);
        } finally {
            $book->disconnectWorksheets();
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_complete_template_is_normalized_and_all_source_columns_survive(): void
    {
        $this->login();
        $template = new Sp2dTemplateExport;
        $row = $template->array()[0];
        $second = $row;
        $second[14] = 'Uraian BKU berbeda dari SP2D';
        $this->upload($template->headings(), [$row, $second])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertDatabaseCount('budget_affairs', 1);
        $this->assertDatabaseCount('budget_programs', 1);
        $this->assertDatabaseCount('budget_activities', 1);
        $this->assertDatabaseCount('budget_subactivities', 1);
        $this->assertDatabaseCount('sp2d_budget_codes', 2);
        $this->assertDatabaseCount('sp2d_import_rows', 2);
        $codes = Sp2dBudgetCode::orderBy('id')->get();
        $this->assertSame($codes[0]->budget_subactivity_id, $codes[1]->budget_subactivity_id);
        $this->assertEquals('1117200000.00', Sp2d::first()->jumlah_total);
        $this->assertSame($row[3], $codes[0]->subactivity->activity->program->name);
        $this->assertSame($row[0], $codes[0]->subactivity->activity->program->affair->name);
        $source = $codes[1]->importRow;
        $this->assertSame($template->headings(), $source->headers);
        $this->assertSame($row[10], $source->values[10]);
        $this->assertSame($second[14], $source->values[14]);
        $this->assertSame('00001-B01', $source->values[12]);
        $this->assertSame(3, $source->row_number);
        $this->get(route('sp2d.show', Sp2d::first()))->assertOk()->assertSee($row[3])->assertSee($row[5]);

        // Re-importing an existing SP2D must not duplicate masters or detail rows.
        $this->upload($template->headings(), [$row])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('sp2d_budget_codes', 2);
        $this->assertDatabaseCount('budget_programs', 1);
    }

    public function test_funding_column_is_imported_as_full_line_allocation(): void
    {
        $this->login();
        $template = new Sp2dTemplateExport;
        $row = $template->array()[0];
        $row[array_search('SUMBER_DANA', $template->headings(), true)] = 'Dana Uji';
        $this->upload($template->headings(), [$row])->assertSessionHasNoErrors();
        $code = Sp2dBudgetCode::firstOrFail();
        $this->assertSame('Dana Uji', $code->fundingAllocations()->first()->fundingSource->name);
        $this->assertSame('558600000.00', $code->fundingAllocations()->first()->amount);
        $this->assertDatabaseCount('funding_sources', 1);
    }

    public function test_same_child_names_under_different_parents_are_not_merged(): void
    {
        $this->login();
        $template = new Sp2dTemplateExport;
        $row = $template->array()[0];
        $second = $row;
        $second[3] = 'Program lain';
        $this->upload($template->headings(), [$row, $second])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('budget_programs', 2);
        $this->assertDatabaseCount('budget_activities', 2);
        $this->assertDatabaseCount('budget_subactivities', 2);
        $this->assertNotEquals(...Sp2dBudgetCode::pluck('budget_subactivity_id')->all());
    }

    public function test_incomplete_hierarchy_rejects_entire_file_without_partial_writes(): void
    {
        $this->login();
        $template = new Sp2dTemplateExport;
        $row = $template->array()[0];
        $bad = $row;
        $bad[5] = '';
        $this->upload($template->headings(), [$row, $bad])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('sp2d', 0);
        $this->assertDatabaseCount('opds', 0);
        $this->assertDatabaseCount('budget_affairs', 0);
    }

    public function test_legacy_file_has_explicit_unknown_hierarchy_and_source_is_kept(): void
    {
        $this->login();
        $headers = ['KDUNIT', 'NMUNIT', 'KDPER', 'NMPER', 'NOSP2D', 'TGL_SP2D', 'NILAI_SP2D'];
        $this->upload($headers, [['01', 'Dinas', '5.1', 'Belanja', 'SP2D-1', '2026-01-01', 100]])
            ->assertSessionHasNoErrors()->assertSessionHas('success', fn ($text) => str_contains($text, 'belum memiliki informasi'));
        $this->assertNull(Sp2dBudgetCode::first()->budget_subactivity_id);
        $this->assertDatabaseCount('sp2d_import_rows', 1);
        $this->assertDatabaseCount('budget_affairs', 0);
    }

    public function test_migration_preserves_legacy_rows_and_does_not_invent_hierarchy(): void
    {
        $this->login();
        $opd = Opd::create(['nama_opd' => 'Dinas lama']);
        $sp2d = Sp2d::create(['opd_id' => $opd->id, 'nomor_sp2d' => 'OLD', 'tanggal' => '2026-01-01', 'tahun' => 2026, 'triwulan' => 1, 'jumlah_total' => 100, 'created_by' => auth()->id()]);
        $code = $sp2d->budgetCodes()->create(['kode_belanja' => '5.1', 'uraian' => 'Lama', 'jumlah' => 100]);
        $migration = require database_path('migrations/2026_10_11_100000_normalize_sp2d_budget_hierarchy.php');
        $migration->down();
        $migration->up();
        $this->assertDatabaseHas('sp2d_budget_codes', ['id' => $code->id, 'sp2d_id' => $sp2d->id, 'jumlah' => 100, 'budget_subactivity_id' => null]);
        $this->assertDatabaseHas('opds', ['id' => $opd->id, 'nama_opd' => 'Dinas lama']);
        $this->assertDatabaseCount('budget_subactivities', 0);
    }

    public function test_referenced_hierarchy_cannot_be_deleted(): void
    {
        $affair = BudgetAffair::create(['name' => 'Urusan']);
        BudgetProgram::create(['budget_affair_id' => $affair->id, 'name' => 'Program']);
        $this->expectException(QueryException::class);
        $affair->delete();
    }
}
