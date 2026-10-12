<?php

namespace App\Http\Controllers\Sp2d;

use App\Http\Controllers\Controller;
use App\Models\FundingSource;
use App\Models\Sp2d;
use App\Models\Sp2dBudgetCode;
use App\Services\ActivityLogger;
use App\Services\FundingAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Sp2dFundingController extends Controller
{
    public function edit(Sp2d $sp2d, Sp2dBudgetCode $budgetCode)
    {
        abort_unless($budgetCode->sp2d_id === $sp2d->id, 404);
        $budgetCode->load('fundingAllocations.fundingSource');

        return view('sp2d.funding', [
            'sp2d' => $sp2d,
            'budgetCode' => $budgetCode,
            'sources' => FundingSource::orderBy('name')->pluck('name'),
            'allocations' => $budgetCode->fundingAllocations->map(fn ($row) => ['name' => $row->fundingSource->name, 'amount' => $row->amount])->values()->all(),
        ]);
    }

    public function update(Request $request, Sp2d $sp2d, Sp2dBudgetCode $budgetCode, FundingAllocationService $service)
    {
        abort_unless($budgetCode->sp2d_id === $sp2d->id, 404);
        $data = $request->validate([
            'allocations' => 'sometimes|array|max:30',
            'allocations.*.name' => 'required|string|max:255',
            'allocations.*.amount' => ['required', 'regex:/^\d{1,13}(?:\.\d{1,2})?$/D'],
            'confirm_funding' => 'required|accepted',
        ]);
        DB::transaction(function () use ($sp2d, $budgetCode, $data, $service) {
            $parent = Sp2d::whereKey($sp2d->id)->lockForUpdate()->firstOrFail();
            $code = $parent->budgetCodes()->whereKey($budgetCode->id)->lockForUpdate()->firstOrFail();
            if ($parent->spj()->where('status', 'reviewed')->exists()) {
                throw ValidationException::withMessages(['allocations' => 'SP2D sudah selesai direviu. Buka kembali reviu sebelum mengubah sumber dana.']);
            }
            $before = $code->fundingAllocations()->with('fundingSource')->get()->map(fn ($row) => ['name' => $row->fundingSource->name, 'amount' => $row->amount])->all();
            $service->replace($code, $data['allocations'] ?? []);
            ActivityLogger::log('update', 'sp2d', 'Mengubah alokasi sumber dana rincian #'.$code->id.': '.json_encode(['before' => $before, 'after' => $data['allocations'] ?? []], JSON_UNESCAPED_UNICODE), $parent);
        });

        return redirect()->route('sp2d.show', $sp2d)->with('success', 'Alokasi sumber dana berhasil disimpan.');
    }
}
