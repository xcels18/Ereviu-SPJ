<?php

namespace App\Models;

use App\Support\RequiredDocuments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ReviewGuide extends Model
{
    use HasFactory;

    protected $fillable = ['kode_rekening', 'nama_belanja', 'required_documents', 'notes', 'is_active', 'created_by'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $with = ['requirements'];

    protected $appends = ['required_documents'];

    private ?array $pendingDocuments = null;

    public function requirements(): HasMany
    {
        return $this->hasMany(ReviewGuideRequirement::class)->orderBy('position');
    }

    public function getRequiredDocumentsAttribute(): array
    {
        return $this->pendingDocuments ?? $this->requirements->pluck('name')->all();
    }

    public function setRequiredDocumentsAttribute($value): void
    {
        $this->pendingDocuments = RequiredDocuments::decode($value);
    }

    public function save(array $options = [])
    {
        return DB::transaction(function () use ($options) {
            BudgetAccount::firstOrCreate(['code' => $this->kode_rekening]);
            $saved = parent::save($options);
            if ($saved && $this->pendingDocuments !== null) {
                $this->requirements()->delete();
                $this->requirements()->createMany(array_map(fn ($name, $position) => ['name' => $name, 'position' => $position], $this->pendingDocuments, array_keys($this->pendingDocuments)));
                $this->pendingDocuments = null;
                $this->unsetRelation('requirements');
            }

            return $saved;
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
