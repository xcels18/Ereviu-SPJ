<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sp2dBudgetCode extends Model
{
    use HasFactory;

    protected $table = 'sp2d_budget_codes';

    protected $fillable = ['sp2d_id', 'kode_belanja', 'uraian', 'jumlah'];

    public function sp2d(): BelongsTo
    {
        return $this->belongsTo(Sp2d::class, 'sp2d_id');
    }

    public function spj(): HasMany
    {
        return $this->hasMany(Spj::class, 'sp2d_budget_code_id');
    }
}