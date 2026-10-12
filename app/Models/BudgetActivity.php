<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetActivity extends Model
{
    protected $table = 'budget_activities';

    protected $fillable = ['name', 'budget_program_id'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(BudgetProgram::class, 'budget_program_id');
    }

    public function subactivities(): HasMany
    {
        return $this->hasMany(BudgetSubactivity::class, 'budget_activity_id');
    }
}
