<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class MfaService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    /**
     * Generate a new random TOTP secret.
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Build the otpauth:// provisioning URL for the given user & secret.
     */
    public function getQrCodeUrl(User $user, string $secret): string
    {
        $issuer = config('app.name', 'Laravel');

        return $this->google2fa->getQRCodeUrl($issuer, $user->email, $secret);
    }

    /**
     * Render the QR code as an inline SVG image.
     */
    public function getQrCodeSvg(string $otpAuthUrl): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);

        return $writer->writeString($otpAuthUrl);
    }

    /**
     * Verify a one-time password against a secret.
     */
    public function verifyKey(string $secret, string $otp): bool
    {
        return (bool) $this->google2fa->verifyKey($secret, $otp);
    }

    /**
     * Generate a fresh set of recovery codes (plain, to be shown once).
     *
     * @return array<int, string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->all();
    }

    /**
     * Check whether the given code matches one of the user's recovery codes.
     * Returns true and removes the used code from storage if valid.
     */
    public function useRecoveryCode(User $user, string $code): bool
    {
        return DB::transaction(function () use ($user, $code) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $codes = $locked->mfa_recovery_codes ?? [];
            $normalized = Str::upper(trim($code));
            if (! in_array($normalized, $codes, true)) {
                return false;
            }
            $locked->mfa_recovery_codes = array_values(array_diff($codes, [$normalized]));
            $locked->save();

            return true;
        });
    }
}
