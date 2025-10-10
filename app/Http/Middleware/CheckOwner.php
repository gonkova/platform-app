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
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthorized. Please login.'
            ], 401);
        }

        if ($request->user()->role->name !== 'owner') {
            return response()->json([
                'message' => 'Forbidden. Only owners can perform this action.',
                'your_role' => $request->user()->role->name
            ], 403);
        }

        return $next($request);
    }
}