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
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Проверка дали потребителят е логнат
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthorized. Please login.'
            ], 401);
        }

        // Проверка дали потребителят има някоя от разрешените роли
        $userRole = $request->user()->role->name;

        if (!in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'Forbidden. You do not have permission to access this resource.',
                'your_role' => $userRole,
                'required_roles' => $roles
            ], 403);
        }

        return $next($request);
    }
}