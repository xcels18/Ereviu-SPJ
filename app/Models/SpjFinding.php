<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class SpjFinding extends Model
{
    use HasFactory;

    protected $table = 'spj_findings';

    protected $fillable = ['spj_id', 'sp2d_budget_code_id', 'finding_type', 'status_bukti', 'description', 'created_by'];

    protected static function booted(): void
    {
        static::saving(function ($record) {
            if ($record->sp2d_budget_code_id !== null) {
                $spj = Spj::findOrFail($record->spj_id);
                if (! Sp2dBudgetCode::whereKey($record->sp2d_budget_code_id)->where('sp2d_id', $spj->sp2d_id)->exists()) {
                    throw ValidationException::withMessages(['budget_codes' => 'Akun dokumen/penilaian harus berasal dari SP2D induk SPJ.']);
                }
            }
        });
    }

    public function spj(): BelongsTo
    {
        return $this->belongsTo(Spj::class, 'spj_id');
    }

    public function budgetCode(): BelongsTo
    {
        return $this->belongsTo(Sp2dBudgetCode::class, 'sp2d_budget_code_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
