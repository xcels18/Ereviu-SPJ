<?php

namespace App\Providers;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Audit trail: catat login, logout, dan percobaan login gagal.
        Event::listen(Login::class, function (Login $event) {
            ActivityLogger::log(
                'login',
                'auth',
                "Login berhasil: {$event->user->name} ({$event->user->email})",
                null,
                $event->user
            );
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                ActivityLogger::log(
                    'logout',
                    'auth',
                    "Logout: {$event->user->name} ({$event->user->email})",
                    null,
                    $event->user
                );
            }
        });

        Event::listen(Failed::class, function (Failed $event) {
            $email = $event->credentials['email'] ?? '-';
            ActivityLogger::log(
                'login_failed',
                'auth',
                "Percobaan login gagal untuk email: {$email}",
                null,
                $event->user
            );
        });
    }
}
