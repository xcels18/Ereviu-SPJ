<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sp2dFundingAllocation extends Model
{
    protected $fillable = ['funding_source_id', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function fundingSource(): BelongsTo
    {
        return $this->belongsTo(FundingSource::class);
    }

    public function budgetCode(): BelongsTo
    {
        return $this->belongsTo(Sp2dBudgetCode::class, 'sp2d_budget_code_id');
    }
}
