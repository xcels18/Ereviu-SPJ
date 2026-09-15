<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'opd_id', 'role', 'parent_irban_id'];

    protected $hidden = ['password', 'remember_token', 'mfa_secret', 'mfa_recovery_codes'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mfa_enabled' => 'boolean',
            'mfa_secret' => 'encrypted',
            'mfa_recovery_codes' => 'encrypted:array',
            'mfa_confirmed_at' => 'datetime',
        ];
    }

    public function getPrimaryRoleAttribute(): ?string
    {
        return $this->roles->sortBy('id')->first()?->name;
    }

    protected static function booted(): void
    {
        static::deleting(fn (User $user) => $user->assertCanDelete());
    }

    public function assertCanDelete(): void
    {
        if (($this->hasRole('admin') && static::role('admin')->count() <= 1) || static::where('parent_irban_id', $this->id)->exists()) {
            throw ValidationException::withMessages(['user' => 'Admin terakhir atau Irban yang masih memiliki operator tidak dapat dihapus.']);
        }
        foreach ([['sp2d', 'created_by'], ['spj_documents', 'uploaded_by'], ['spj_findings', 'created_by']] as [$table, $column]) {
            if (DB::table($table)->where($column, $this->id)->exists()) {
                throw ValidationException::withMessages(['user' => 'Akun masih terhubung dengan arsip SP2D/SPJ dan tidak dapat dihapus.']);
            }
        }
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class, 'opd_id');
    }

    public function auditorOpdAssignments(): HasMany
    {
        return $this->hasMany(AuditorOpdAssignment::class, 'user_id');
    }

    /**
     * Irban induk dari akun Operator Irban ini (null untuk role lain).
     */
    public function parentIrban(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_irban_id');
    }

    /**
     * True untuk role irban maupun operator_irban, karena keduanya dibatasi
     * pada wilayah kerja (bukan admin/inspektur yang tanpa batas, atau
     * opd_admin/kepala_opd yang terikat pada satu OPD sendiri).
     */
    public function isIrbanLike(): bool
    {
        return $this->hasRole('irban') || $this->hasRole('operator_irban');
    }

    /**
     * ID user irban yang menjadi acuan wilayah kerja (auditor_opd_assignments).
     * Untuk role irban, acuannya diri sendiri. Untuk operator_irban, acuannya
     * adalah Irban induk yang dipilih saat pembuatan akun. Selain itu null.
     */
    public function irbanScopeUserId(): ?int
    {
        if ($this->hasRole('irban')) {
            return $this->id;
        }

        if ($this->hasRole('operator_irban')) {
            return $this->parent_irban_id;
        }

        return null;
    }
}
