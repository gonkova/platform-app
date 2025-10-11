<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AiTool;

class CheckResourceOwner
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

        // Owner може всичко
        if ($roleName === 'owner') {
            return $next($request);
        }

        // ID на ресурса от route параметъра
        $resourceId = $request->route('id') ?? $request->route('aiTool');

        if (!$resourceId) {
            return response()->json([
                'message' => 'Resource not found.'
            ], 404);
        }

        $aiTool = AiTool::find($resourceId);

        if ($aiTool && $aiTool->created_by === $user->id) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Forbidden. You can only modify your own resources.'
        ], 403);
    }
}
