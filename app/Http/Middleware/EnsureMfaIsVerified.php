<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMfaIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->mfa_enabled && ! $request->session()->get('mfa_verified')) {
            $request->session()->put('mfa_user_id', $user->id);

            auth()->logout();

            return redirect()->route('mfa.challenge');
        }

        return $next($request);
    }
}
