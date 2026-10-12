<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetSubactivity extends Model
{
    protected $table = 'budget_subactivities';

    protected $fillable = ['name', 'budget_activity_id'];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(BudgetActivity::class, 'budget_activity_id');
    }

    public function budgetCodes(): HasMany
    {
        return $this->hasMany(Sp2dBudgetCode::class, 'budget_subactivity_id');
    }
}
