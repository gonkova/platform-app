<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckResourceOwner
{
    /**
     * Handle an incoming request.
     * Проверява дали потребителят е собственик на ресурса
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthorized. Please login.'
            ], 401);
        }

        // Вземаме ID на ресурса от route параметъра
        $resourceId = $request->route('id') ?? $request->route('aiTool');
        
        if (!$resourceId) {
            return response()->json([
                'message' => 'Resource not found.'
            ], 404);
        }

        // Проверяваме дали ресурсът принадлежи на текущия потребител
        // Или дали потребителят е Owner (може да редактира всичко)
        $user = $request->user();
        
        if ($user->role->name === 'owner') {
            return $next($request); // Owner може всичко
        }

        // Проверяваме дали ресурсът принадлежи на текущия потребител
        $aiTool = \App\Models\AiTool::find($resourceId);
        if ($aiTool && $aiTool->created_by === $user->id) {
            return $next($request);
        }

        // Тук можем да добавим проверка за конкретния модел
        // Засега просто връщаме forbidden за не-owners
        return response()->json([
            'message' => 'Forbidden. You can only modify your own resources.'
        ], 403);
    }
}