<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Catat satu aktivitas pengguna ke audit trail.
     *
     * @param  string  $action  Kata kerja singkat, mis. 'create','update','delete','login','logout','login_failed','upload','review','import','compile'.
     * @param  string  $module  Modul terkait, mis. 'auth','user','opd','sp2d','spj','review_guide','spj_compile'.
     * @param  string  $description  Deskripsi ringkas dan jelas untuk ditampilkan di halaman audit trail.
     * @param  mixed  $subject  Model terkait (opsional), dipakai untuk mencatat subject_type & subject_id.
     * @param  User|null  $actor  Pelaku aktivitas. Default: user yang sedang login.
     */
    public static function log(string $action, string $module, string $description, mixed $subject = null, ?User $actor = null): ActivityLog
    {
        $user = $actor ?? auth()->user();

        return ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'user_role' => $user?->getRoleNames()->implode(', '),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::header('User-Agent'), 0, 255),
        ]);
    }
}
