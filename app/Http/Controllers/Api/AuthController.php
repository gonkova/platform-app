<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use App\Services\ActivityLogger;

class AuthController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Грешен имейл или парола.'],
            ]);
        }

        $user->load('role');

        // Проверка за 2FA
        if ($user->hasTwoFactorEnabled()) {
            $tempToken = $user->createToken('2fa-temp', ['verify-2fa'])->plainTextToken;

            return response()->json([
                'requires_2fa' => true,
                'temp_token' => $tempToken,
                'message' => 'Моля въведете 6-цифрен код от вашето authenticator приложение',
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        // ✅ Логваме login с подадения user
        ActivityLogger::logLogin($user);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? [
                    'name' => $user->role->name,
                    'display_name' => $user->role->display_name,
                    'description' => $user->role->description,
                ] : null,
                'two_factor_enabled' => $user->hasTwoFactorEnabled(),
            ],
            'token' => $token,
        ]);
    }

    // Verify 2FA code
    public function verify2fa(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => '2FA не е активирана за този потребител'
            ], 400);
        }

        $code = $request->code;
        $secret = decrypt($user->two_factor_secret);

        $valid = $this->google2fa->verifyKey($secret, $code);

        if (!$valid) {
            $valid = $user->useBackupCode($code);

            if (!$valid) {
                return response()->json([
                    'message' => 'Невалиден код. Моля опитайте отново.'
                ], 400);
            }
        }

        $user->tokens()->where('name', '2fa-temp')->delete();
        $token = $user->createToken('auth-token')->plainTextToken;
        $user->load('role');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? [
                    'name' => $user->role->name,
                    'display_name' => $user->role->display_name,
                    'description' => $user->role->description,
                ] : null,
                'two_factor_enabled' => $user->hasTwoFactorEnabled(),
            ],
            'token' => $token,
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        ActivityLogger::logLogout();

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Успешно излязохте от системата.',
        ]);
    }

    // Get authenticated user
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('role');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? [
                    'name' => $user->role->name,
                    'display_name' => $user->role->display_name,
                    'description' => $user->role->description,
                ] : null,
                'two_factor_enabled' => $user->hasTwoFactorEnabled(),
            ],
        ]);
    }
}
