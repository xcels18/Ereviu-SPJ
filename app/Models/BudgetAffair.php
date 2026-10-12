<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetAffair extends Model
{
    protected $table = 'budget_affairs';

    protected $fillable = ['name'];

    public function programs(): HasMany
    {
        return $this->hasMany(BudgetProgram::class, 'budget_affair_id');
    }
}
