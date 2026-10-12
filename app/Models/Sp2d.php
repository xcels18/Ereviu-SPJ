<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sp2d extends Model
{
    use HasFactory;

    protected $table = 'sp2d';

    protected $fillable = ['opd_id', 'nomor_sp2d', 'tahun', 'triwulan', 'tanggal', 'jumlah_total', 'created_by'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    protected $appends = ['tahun', 'triwulan'];

    public function getTahunAttribute(): ?int
    {
        return $this->tanggal?->year;
    }

    public function getTriwulanAttribute(): ?int
    {
        return $this->tanggal?->quarter;
    }

    // Compatibility with historical seeders; period is always derived from tanggal.
    public function setTahunAttribute($value): void {}

    public function setTriwulanAttribute($value): void {}

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function budgetCodes(): HasMany
    {
        return $this->hasMany(Sp2dBudgetCode::class, 'sp2d_id');
    }

    public function spj(): HasMany
    {
        return $this->hasMany(Spj::class, 'sp2d_id');
    }
}
