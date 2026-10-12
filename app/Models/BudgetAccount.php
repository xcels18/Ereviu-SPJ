<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetAccount extends Model
{
    protected $fillable = ['code'];

    public function budgetCodes(): HasMany
    {
        return $this->hasMany(Sp2dBudgetCode::class);
    }
}
