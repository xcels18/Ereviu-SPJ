<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewGuide extends Model
{
    use HasFactory;

    protected $fillable = ['kode_rekening', 'nama_belanja', 'required_documents', 'notes', 'is_active', 'created_by'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getRequiredDocumentsAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $decoded2 = json_decode($decoded, true);

        if (is_array($decoded2)) {
            return $decoded2;
        }

        return array_map('trim', explode(',', $value));
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
