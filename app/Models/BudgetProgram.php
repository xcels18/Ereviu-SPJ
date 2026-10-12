<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetProgram extends Model
{
    protected $table = 'budget_programs';

    protected $fillable = ['name', 'budget_affair_id'];

    public function affair(): BelongsTo
    {
        return $this->belongsTo(BudgetAffair::class, 'budget_affair_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(BudgetActivity::class, 'budget_program_id');
    }
}
