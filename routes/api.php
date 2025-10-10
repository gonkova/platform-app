<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AiToolController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/roles', function() {
    return response()->json(\App\Models\Role::all());
});

// Categories - public access
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// AI Tools - public listing
Route::get('/ai-tools', [AiToolController::class, 'index']);
Route::get('/ai-tools/{id}', [AiToolController::class, 'show']);

// Protected routes - всички authenticated потребители
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // AI Tools management - всички могат да създават
    Route::post('/ai-tools', [AiToolController::class, 'store']);
    Route::put('/ai-tools/{id}', [AiToolController::class, 'update']);
});

// Owner only routes - само за owner роля
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    // Categories management - само owner
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    
    // Delete tools - само owner може да трие
    Route::delete('/ai-tools/{id}', [AiToolController::class, 'destroy']);
    
    // Approve/Reject tools
    Route::post('/ai-tools/{id}/approve', [AiToolController::class, 'approve']);
    Route::post('/ai-tools/{id}/reject', [AiToolController::class, 'reject']);
});