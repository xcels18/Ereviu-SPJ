<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SpjExport extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['selection' => 'array', 'parts' => 'array', 'started_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    public function directory(): string
    {
        return storage_path('app/private/spj-exports/'.$this->id);
    }
}
