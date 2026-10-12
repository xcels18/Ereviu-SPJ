<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Validation\ValidationException;

class Spj extends Model
{
    use HasFactory;

    protected $table = 'spj';

    protected $fillable = [
        'sp2d_id', 'opd_id', 'sp2d_budget_code_id', 'nomor_spj', 'tanggal', 'jumlah',
        'review_version', 'review_mode', 'status', 'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    private ?int $requestedOpdId = null;

    public function getOpdIdAttribute(): ?int
    {
        return $this->sp2d?->opd_id;
    }

    public function setOpdIdAttribute($value): void
    {
        $this->requestedOpdId = $value === null ? null : (int) $value;
    }

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'reviewed_at' => 'datetime',
            'jumlah' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Spj $spj) {
            $parent = Sp2d::findOrFail($spj->sp2d_id);
            if ($spj->requestedOpdId !== null && $spj->requestedOpdId !== (int) $parent->opd_id) {
                throw ValidationException::withMessages(['opd_id' => 'OPD SPJ harus sama dengan OPD SP2D induk.']);
            }
            if ($spj->sp2d_budget_code_id !== null && ! Sp2dBudgetCode::whereKey($spj->sp2d_budget_code_id)->where('sp2d_id', $parent->id)->exists()) {
                throw ValidationException::withMessages(['budget_codes' => 'Akun SPJ harus berasal dari SP2D induk.']);
            }
        });
    }

    public function sp2d(): BelongsTo
    {
        return $this->belongsTo(Sp2d::class);
    }

    public function opd(): HasOneThrough
    {
        return $this->hasOneThrough(Opd::class, Sp2d::class, 'id', 'id', 'sp2d_id', 'opd_id');
    }

    public function budgetCode(): BelongsTo
    {
        return $this->belongsTo(Sp2dBudgetCode::class, 'sp2d_budget_code_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SpjDocument::class, 'spj_id');
    }

    public function sp2dScanDocuments(): HasMany
    {
        return $this->hasMany(SpjDocument::class, 'spj_id')->where('document_type', 'sp2d_scan');
    }

    public function buktiBelanjaDocuments(): HasMany
    {
        return $this->hasMany(SpjDocument::class, 'spj_id')->where('document_type', 'bukti_belanja');
    }

    public function otherDocuments(): HasMany
    {
        return $this->hasMany(SpjDocument::class, 'spj_id')->where('document_type', 'lainnya');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(SpjFinding::class, 'spj_id');
    }
}
