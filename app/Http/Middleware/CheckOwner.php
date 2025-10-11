<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOwner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized. Please login.'
            ], 401);
        }

        $roleName = $user->role->name ?? null;

        if ($roleName !== 'owner') {
            return response()->json([
                'message' => 'Forbidden. Only owners can perform this action.',
                'your_role' => $roleName
            ], 403);
        }

        return $next($request);
    }
}
