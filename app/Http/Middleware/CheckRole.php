<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized. Please login.'
            ], 401);
        }

        // Проверка дали потребителят има роля
        if (!$user->role) {
            return response()->json([
                'message' => 'Forbidden. User has no assigned role.',
                'required_roles' => $roles
            ], 403);
        }

        $userRole = $user->role->name;

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
