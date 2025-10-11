<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AiToolController;
use App\Http\Controllers\Api\TwoFactorController;
use App\Http\Controllers\Api\ActivityController; // ✅ ДОБАВЕНО
use Illuminate\Support\Facades\Route;

// ===== PUBLIC ROUTES (без auth) =====
Route::post('/login', [AuthController::class, 'login']);

// ===== 2FA VERIFICATION (използва temp token) =====
Route::post('/verify-2fa', [AuthController::class, 'verify2fa'])
    ->middleware('auth:sanctum', 'ability:verify-2fa');

// ===== AUTHENTICATED ROUTES =====
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth endpoints - всички authenticated потребители
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // 2FA Management
    Route::prefix('2fa')->group(function () {
        Route::get('/status', [TwoFactorController::class, 'status']);
        Route::post('/enable', [TwoFactorController::class, 'enable']);
        Route::post('/confirm', [TwoFactorController::class, 'confirm']);
        Route::post('/disable', [TwoFactorController::class, 'disable']);
        Route::post('/backup-codes', [TwoFactorController::class, 'regenerateBackupCodes']);
    });

    // Categories и Roles - всички authenticated могат да четат
    Route::get('/categories', function () {
        return response()->json(\App\Models\Category::all());
    });
    
    Route::get('/roles', function () {
        return response()->json(\App\Models\Role::all());
    });

    // AI Tools - READ операции (всички authenticated)
    Route::get('/ai-tools', [AiToolController::class, 'index']);
    Route::get('/ai-tools/{id}', [AiToolController::class, 'show']);

    // AI Tools - WRITE операции (всички authenticated могат да създават)
    Route::post('/ai-tools', [AiToolController::class, 'store']);

    // AI Tools - UPDATE/DELETE (само owner на ресурса или Owner роля)
    Route::middleware('resource.owner')->group(function () {
        Route::put('/ai-tools/{id}', [AiToolController::class, 'update']);
        Route::delete('/ai-tools/{id}', [AiToolController::class, 'destroy']);
    });

    // ===== OWNER ONLY ROUTES =====
    Route::middleware('owner')->group(function () {
        
        // Admin панел за одобрение на tools
        Route::get('/admin/tools/pending', [AiToolController::class, 'pending']);
        Route::post('/admin/tools/{id}/approve', [AiToolController::class, 'approve']);
        Route::post('/admin/tools/{id}/reject', [AiToolController::class, 'reject']);
        
        // Bulk операции
        Route::post('/admin/tools/bulk-approve', [AiToolController::class, 'bulkApprove']);
        Route::post('/admin/tools/bulk-reject', [AiToolController::class, 'bulkReject']);

        // ✅ ACTIVITY LOG ROUTES (Owner only)
        Route::get('/activities', [ActivityController::class, 'index']);
        Route::get('/activities/stats', [ActivityController::class, 'stats']);
        Route::get('/activities/{id}', [ActivityController::class, 'show']);
    });

    // ✅ My Activities (всички authenticated потребители)
    Route::get('/my-activities', [ActivityController::class, 'myActivities']);

    // ===== ROLE-SPECIFIC ROUTES (примерни) =====
    
    // Frontend роля - достъп до frontend ресурси
    Route::middleware('role:owner,frontend')->group(function () {
        Route::get('/frontend/resources', function () {
            return response()->json(['message' => 'Frontend resources']);
        });
    });

    // Backend роля - достъп до backend ресурси
    Route::middleware('role:owner,backend')->group(function () {
        Route::get('/backend/resources', function () {
            return response()->json(['message' => 'Backend resources']);
        });
    });
});
