<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Opd extends Model
{
    use HasFactory;

    protected $fillable = ['nama_opd', 'singkatan', 'kode_unit'];

    protected static function booted(): void
    {
        static::deleting(function (Opd $opd) {
            if ($opd->sp2d()->exists()) {
                throw ValidationException::withMessages(['opd' => 'OPD masih memiliki arsip SP2D dan tidak dapat dihapus.']);
            }
        });
    }

    public function sp2d(): HasMany
    {
        return $this->hasMany(Sp2d::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function auditorAssignments(): HasMany
    {
        return $this->hasMany(AuditorOpdAssignment::class, 'opd_id');
    }
}
