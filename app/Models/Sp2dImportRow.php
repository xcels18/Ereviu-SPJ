<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sp2dImportRow extends Model
{
    protected $fillable = ['sp2d_budget_code_id', 'source_file', 'row_number', 'headers', 'values'];

    protected function casts(): array
    {
        return ['headers' => 'array', 'values' => 'array'];
    }

    public function budgetCode(): BelongsTo
    {
        return $this->belongsTo(Sp2dBudgetCode::class, 'sp2d_budget_code_id');
    }
}
