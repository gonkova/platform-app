<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Генериране на 2FA secret и QR код
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => '2FA вече е активирана'
            ], 400);
        }

        $secret = $this->google2fa->generateSecretKey();
        $user->two_factor_secret = encrypt($secret);
        $user->save();

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'message' => 'Сканирайте QR кода с Google Authenticator приложението'
        ]);
    }

    /**
     * Потвърждаване на 2FA с код от приложението
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = $request->user();

        if (!$user->two_factor_secret) {
            return response()->json([
                'message' => 'Няма генериран 2FA secret. Първо активирайте 2FA.'
            ], 400);
        }

        $secret = decrypt($user->two_factor_secret);
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return response()->json([
                'message' => 'Невалиден код. Моля опитайте отново.'
            ], 400);
        }

        // Генерирай backup codes
        $backupCodes = $this->generateBackupCodes();

        // Активирай 2FA
        $user->two_factor_enabled = true;
        $user->two_factor_confirmed_at = now();
        $user->two_factor_backup_codes = $backupCodes;
        $user->save();

        // LOG 2FA ENABLED
        ActivityLogger::log2FAEnabled();

        return response()->json([
            'message' => '2FA успешно активирана!',
            'backup_codes' => $backupCodes
        ]);
    }

    /**
     * Деактивиране на 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Невалидна парола'
            ], 400);
        }

        // Деактивирай 2FA
        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->two_factor_backup_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        // LOG 2FA DISABLED
        ActivityLogger::log2FADisabled();

        return response()->json([
            'message' => '2FA успешно деактивирана'
        ]);
    }

    /**
     * Статус на 2FA
     */
    public function status(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'enabled' => $user->hasTwoFactorEnabled(),
            'confirmed_at' => $user->two_factor_confirmed_at,
            'has_backup_codes' => !empty($user->two_factor_backup_codes)
        ]);
    }

    /**
     * Регенериране на backup codes
     */
    public function regenerateBackupCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => '2FA не е активирана'
            ], 400);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Невалидна парола'
            ], 400);
        }

        $backupCodes = $this->generateBackupCodes();
        $user->two_factor_backup_codes = $backupCodes;
        $user->save();

        return response()->json([
            'message' => 'Backup кодовете са регенерирани',
            'backup_codes' => $backupCodes
        ]);
    }

    /**
     * Генериране на backup codes
     */
    protected function generateBackupCodes(): array
    {
        $codes = [];

        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(Str::random(4) . '-' . Str::random(4));
        }

        return $codes;
    }
}
