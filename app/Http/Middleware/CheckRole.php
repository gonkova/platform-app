<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Провери дали потребителят е логнат
        if (!$request->user()) {
            return response()->json([
                'message' => 'Неоторизиран достъп. Моля, влезте в системата.',
            ], 401);
        }

        // Зареди роля ако не е заредена
        $user = $request->user();
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        // Провери дали потребителят има някоя от позволените роли
        $userRole = $user->role->name;
        
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'Нямате права за достъп до този ресурс.',
                'required_roles' => $roles,
                'your_role' => $userRole,
            ], 403);
        }

        return $next($request);
    }
}