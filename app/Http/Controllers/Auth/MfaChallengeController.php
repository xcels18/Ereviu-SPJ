<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MfaChallengeController extends Controller
{
    public function __construct(protected MfaService $mfaService)
    {
    }

    /**
     * Display the MFA challenge form.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('mfa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.mfa-challenge');
    }

    /**
     * Verify the submitted OTP or recovery code and complete the login.
     */
    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('mfa_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = \App\Models\User::findOrFail($userId);

        $code = trim($request->input('code'));
        $verified = $this->mfaService->verifyKey($user->mfa_secret, $code)
            || $this->mfaService->useRecoveryCode($user, $code);

        if (! $verified) {
            throw ValidationException::withMessages([
                'code' => __('Kode verifikasi tidak valid.'),
            ]);
        }

        $request->session()->forget('mfa_user_id');

        Auth::login($user, $request->session()->pull('mfa_remember', false));

        $request->session()->regenerate();
        $request->session()->put('mfa_verified', true);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
