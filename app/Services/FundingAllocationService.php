<?php

namespace App\Services;

use App\Models\FundingSource;
use App\Models\Sp2dBudgetCode;
use Illuminate\Validation\ValidationException;

class FundingAllocationService
{
    // Caller holds the SP2D lock and transaction for writes and their audit event.
    public function replace(Sp2dBudgetCode $code, array $rows): void
    {
        $parser = new Sp2dImportValidator;
        $total = 0;
        $allocations = [];
        foreach ($rows as $row) {
            $name = trim($row['name']);
            if ($name === '' || mb_strlen($name) > 255) {
                throw ValidationException::withMessages(['allocations' => 'Nama sumber dana wajib diisi, maksimal 255 karakter.']);
            }
            if (! preg_match('/^\d{1,13}(?:\.\d{1,2})?$/D', (string) $row['amount'])) {
                throw ValidationException::withMessages(['allocations' => 'Nominal sumber dana harus non-negatif dengan maksimal 2 desimal, tanpa pemisah ribuan.']);
            }
            $cents = $parser->amount($row['amount'], 1);
            $total += $cents;
            $source = FundingSource::firstOrCreate(['name' => $name]);
            if (isset($allocations[$source->id])) {
                throw ValidationException::withMessages(['allocations' => 'Sumber dana yang sama tidak boleh diulang dalam satu rincian.']);
            }
            $allocations[$source->id] = ['funding_source_id' => $source->id, 'amount' => $parser->decimal($cents)];
        }
        if ($rows && $total !== $parser->amount($code->jumlah, 1)) {
            throw ValidationException::withMessages(['allocations' => 'Total alokasi sumber dana harus sama dengan nominal rincian belanja.']);
        }
        $code->fundingAllocations()->delete();
        $code->fundingAllocations()->createMany(array_values($allocations));
    }
}
