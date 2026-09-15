<?php

namespace App\Http\Controllers;

use App\Services\MfaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MfaController extends Controller
{
    public function __construct(protected MfaService $mfaService)
    {
    }

    /**
     * Show the MFA setup form (QR code + secret) for the authenticated user.
     */
    public function setup(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->mfa_enabled) {
            return Redirect::route('profile.edit');
        }

        $secret = $request->session()->get('mfa_setup_secret');

        if (! $secret) {
            $secret = $this->mfaService->generateSecretKey();
            $request->session()->put('mfa_setup_secret', $secret);
        }

        $otpAuthUrl = $this->mfaService->getQrCodeUrl($user, $secret);
        $qrCodeSvg = $this->mfaService->getQrCodeSvg($otpAuthUrl);

        return view('profile.mfa-setup', [
            'secret' => $secret,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * Confirm the OTP code and enable MFA for the user.
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $secret = $request->session()->get('mfa_setup_secret');

        if (! $secret) {
            return Redirect::route('mfa.setup');
        }

        if (! $this->mfaService->verifyKey($secret, trim($request->input('code')))) {
            throw ValidationException::withMessages([
                'code' => __('Kode verifikasi tidak valid.'),
            ]);
        }

        $recoveryCodes = $this->mfaService->generateRecoveryCodes();

        $user = $request->user();
        $user->mfa_secret = $secret;
        $user->mfa_enabled = true;
        $user->mfa_recovery_codes = $recoveryCodes;
        $user->mfa_confirmed_at = now();
        $user->save();

        $request->session()->forget('mfa_setup_secret');
        $request->session()->put('mfa_verified', true);

        return Redirect::route('profile.edit')
            ->with('status', 'mfa-enabled')
            ->with('mfa_recovery_codes_show', $recoveryCodes);
    }

    /**
     * Download the user's recovery codes as a PDF.
     */
    public function downloadRecoveryCodes(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user->mfa_enabled) {
            return Redirect::route('profile.edit');
        }

        $recoveryCodes = $user->mfa_recovery_codes ?? [];

        if (empty($recoveryCodes)) {
            return Redirect::route('profile.edit')
                ->with('status', 'mfa-no-recovery-codes');
        }

        $pdf = Pdf::loadView('profile.mfa-recovery-codes-pdf', [
            'user' => $user,
            'recoveryCodes' => $recoveryCodes,
        ]);

        return $pdf->download('kode-pemulihan-mfa-'.str_replace(['@', '.'], '_', $user->email).'.pdf');
    }

    /**
     * Disable MFA for the authenticated user.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $user->mfa_enabled = false;
        $user->mfa_secret = null;
        $user->mfa_recovery_codes = null;
        $user->mfa_confirmed_at = null;
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'mfa-disabled');
    }
}
