<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;

class Sp2dBudgetCode extends Model
{
    use HasFactory;

    protected $table = 'sp2d_budget_codes';

    protected $fillable = ['sp2d_id', 'kode_belanja', 'uraian', 'jumlah', 'budget_subactivity_id', 'budget_account_id'];

    protected $with = ['account'];

    protected $appends = ['kode_belanja'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(BudgetAccount::class, 'budget_account_id');
    }

    public function getKodeBelanjaAttribute(): ?string
    {
        return $this->account?->code;
    }

    public function setKodeBelanjaAttribute(string $code): void
    {
        $account = BudgetAccount::firstOrCreate(['code' => $code]);
        $this->attributes['budget_account_id'] = $account->id;
        $this->setRelation('account', $account);
    }

    protected static function booted(): void
    {
        static::updating(function (self $code) {
            if ($code->isDirty('jumlah') && number_format((float) $code->jumlah, 2, '.', '') !== number_format((float) $code->getOriginal('jumlah'), 2, '.', '') && $code->fundingAllocations()->exists()) {
                throw ValidationException::withMessages(['budget_codes' => 'Kosongkan alokasi sumber dana terlebih dahulu sebelum mengubah nominal rincian, lalu alokasikan kembali.']);
            }
        });
    }

    public function fundingAllocations(): HasMany
    {
        return $this->hasMany(Sp2dFundingAllocation::class, 'sp2d_budget_code_id');
    }

    public function subactivity(): BelongsTo
    {
        return $this->belongsTo(BudgetSubactivity::class, 'budget_subactivity_id');
    }

    public function importRow(): HasOne
    {
        return $this->hasOne(Sp2dImportRow::class, 'sp2d_budget_code_id');
    }

    public function sp2d(): BelongsTo
    {
        return $this->belongsTo(Sp2d::class, 'sp2d_id');
    }

    public function spj(): HasMany
    {
        return $this->hasMany(Spj::class, 'sp2d_budget_code_id');
    }
}
