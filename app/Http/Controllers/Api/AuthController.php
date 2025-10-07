<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
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

        // Зареди роля
        $user->load('role');

        // Създай токен
        $token = $user->createToken('auth-token')->plainTextToken;

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
            ],
            'token' => $token,
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
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
            ],
        ]);
    }
}
