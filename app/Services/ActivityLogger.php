<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log an activity
     */
    public static function log(
        string $action,
        ?string $modelType = null,
        ?int $modelId = null,
        ?string $description = null,
        ?array $properties = null,
        ?int $userId = null // 🔹 Добавен параметър за ръчно подаване на user_id
    ): Activity {
        return Activity::create([
            'user_id' => $userId ?? Auth::id(), // Ако няма userId, вземи от Auth
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log model creation
     */
    public static function logCreated($model, ?string $description = null): Activity
    {
        return self::log(
            'created',
            get_class($model),
            $model->id,
            $description ?? "Създаде " . class_basename($model),
            ['model' => $model->toArray()]
        );
    }

    /**
     * Log model update
     */
    public static function logUpdated($model, array $oldValues, ?string $description = null): Activity
    {
        return self::log(
            'updated',
            get_class($model),
            $model->id,
            $description ?? "Обнови " . class_basename($model),
            [
                'old' => $oldValues,
                'new' => $model->getChanges()
            ]
        );
    }

    /**
     * Log model deletion
     */
    public static function logDeleted($model, ?string $description = null): Activity
    {
        return self::log(
            'deleted',
            get_class($model),
            $model->id,
            $description ?? "Изтри " . class_basename($model),
            ['model' => $model->toArray()]
        );
    }

    /**
     * Log approval
     */
    public static function logApproved($model, ?string $description = null): Activity
    {
        return self::log(
            'approved',
            get_class($model),
            $model->id,
            $description ?? "Одобри " . class_basename($model)
        );
    }

    /**
     * Log rejection
     */
    public static function logRejected($model, string $reason, ?string $description = null): Activity
    {
        return self::log(
            'rejected',
            get_class($model),
            $model->id,
            $description ?? "Отказа " . class_basename($model),
            ['reason' => $reason]
        );
    }

    /**
     * Log login
     */
    public static function logLogin($user = null): Activity
    {
        $user = $user ?? Auth::user();

        return self::log(
            'login',
            null,
            null,
            ($user ? $user->name : 'Unknown user') . " влезе в системата",
            [],
            $user ? $user->id : null // 🔹 подаден user_id
        );
    }

    /**
     * Log logout
     */
    public static function logLogout($user = null): Activity
    {
        $user = $user ?? Auth::user();

        return self::log(
            'logout',
            null,
            null,
            ($user ? $user->name : 'Unknown user') . " излезе от системата",
            [],
            $user ? $user->id : null
        );
    }

    /**
     * Log 2FA enabled
     */
    public static function log2FAEnabled($user = null): Activity
    {
        $user = $user ?? Auth::user();

        return self::log(
            '2fa_enabled',
            null,
            null,
            ($user ? $user->name : 'Unknown user') . " активира 2FA",
            [],
            $user ? $user->id : null
        );
    }

    /**
     * Log 2FA disabled
     */
    public static function log2FADisabled($user = null): Activity
    {
        $user = $user ?? Auth::user();

        return self::log(
            '2fa_disabled',
            null,
            null,
            ($user ? $user->name : 'Unknown user') . " деактивира 2FA",
            [],
            $user ? $user->id : null
        );
    }
}
