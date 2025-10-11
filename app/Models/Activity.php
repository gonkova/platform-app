<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the model that was affected
     */
    public function model()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }
        return null;
    }

    /**
     * Scope for specific action types
     */
    public function scopeOfAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get a friendly action label
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'Създаде',
            'updated' => 'Обнови',
            'deleted' => 'Изтри',
            'approved' => 'Одобри',
            'rejected' => 'Отказа',
            'login' => 'Влезе в системата',
            'logout' => 'Излезе от системата',
            '2fa_enabled' => 'Активира 2FA',
            '2fa_disabled' => 'Деактивира 2FA',
            default => $this->action,
        };
    }

    /**
     * Get a friendly model name
     */
    public function getModelNameAttribute(): string
    {
        if (!$this->model_type) {
            return '';
        }

        return match($this->model_type) {
            'App\\Models\\AiTool' => 'AI Tool',
            'App\\Models\\User' => 'Потребител',
            'App\\Models\\Category' => 'Категория',
            'App\\Models\\Role' => 'Роля',
            default => class_basename($this->model_type),
        };
    }
}