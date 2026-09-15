<?php

namespace Database\Seeders;

use App\Models\Opd;
use App\Models\Sp2d;
use App\Models\Sp2dBudgetCode;
use App\Models\Spj;
use App\Models\SpjDocument;
use App\Models\SpjFinding;
use Illuminate\Database\Seeder;

class SpjSampleSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample SPJ for testing
        $budgetCode = Sp2dBudgetCode::first();
        if (!$budgetCode) return;

        $spj = Spj::create([
            'sp2d_id' => $budgetCode->sp2d_id,
            'opd_id' => $budgetCode->sp2d->opd_id,
            'sp2d_budget_code_id' => $budgetCode->id,
            'nomor_spj' => 'SPJ-001/2024',
            'tanggal' => now(),
            'jumlah' => $budgetCode->jumlah,
            'status' => 'submitted',
        ]);

        // Add a finding for this SPJ
        SpjFinding::create([
            'spj_id' => $spj->id,
            'finding_type' => 'minor',
            'description' => 'Dokumen kwitansi perlu diperjelas.',
            'created_by' => 1,
        ]);
    }
}