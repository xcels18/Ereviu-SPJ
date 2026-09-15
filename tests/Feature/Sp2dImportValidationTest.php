<?php

namespace Tests\Feature;

use App\Services\Sp2dImportValidator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class Sp2dImportValidationTest extends TestCase
{
    private function columns(): array
    {
        return array_flip(['KDUNIT', 'NMUNIT', 'KDPER', 'NMPER', 'NOSP2D', 'TGL_SP2D', 'NILAI_SP2D']);
    }

    public function test_repeated_codes_remain_separate_and_amounts_are_exact(): void
    {
        $parser = new Sp2dImportValidator;
        $rows = [['01', 'Dinas', '5.1', 'Kertas', '001', '2026-09-01', '0.10'], ['01', 'Dinas', '5.1', 'Tinta', '001', '2026-09-01', '0.20']];
        $groups = $parser->groups($rows, $this->columns());
        $this->assertCount(2, $groups['001']['details']);
        $this->assertSame('0.30', $parser->decimal($groups['001']['total_cents']));
    }

    public function test_conflicting_opd_is_rejected_with_line_number(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Baris 3');
        (new Sp2dImportValidator)->groups([['01', 'Dinas', '5.1', 'Kertas', '001', '2026-09-01', '100'], ['02', 'Lain', '5.1', 'Tinta', '001', '2026-09-01', '200']], $this->columns());
    }

    public function test_invalid_calendar_date_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        (new Sp2dImportValidator)->date('31/02/2026', 2);
    }

    public function test_partial_row_is_not_silently_skipped(): void
    {
        $this->expectException(ValidationException::class);
        (new Sp2dImportValidator)->groups([['01', 'Dinas', '5.1', 'Kertas', '', '2026-09-01', '100']], $this->columns());
    }

    public function test_ambiguous_amount_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        (new Sp2dImportValidator)->amount('1.000,00', 3);
    }
}
