<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'user_email', 'user_role',
        'action', 'module', 'description',
        'subject_type', 'subject_id',
        'ip_address', 'user_agent',
    ];

    /**
     * Relasi ke user pelaku aktivitas. Bisa null jika akun sudah dihapus
     * (data pelaku tetap tersimpan sebagai snapshot di user_name/user_email).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
