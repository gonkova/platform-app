<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiTool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'url',
        'documentation_url',
        'video_url',
        'difficulty_level',
        'logo_url',
        'is_free',
        'price',
        'is_active',
        'status',
        'rejection_reason',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_free' => 'boolean',
        'price' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected $appends = ['status_label', 'status_color'];

    // Auto-generate slug from name
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tool) {
            if (empty($tool->slug)) {
                $tool->slug = \Illuminate\Support\Str::slug($tool->name);
            }
        });
    }

    /**
     * Връзка с потребителя който е създал tool-а
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Връзка с потребителя който е одобрил tool-а
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Връзка many-to-many с categories
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'tool_category');
    }

    /**
     * Връзка many-to-many с roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'tool_role');
    }

    /**
     * Scope за филтриране по статус
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope за одобрени tools
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope за pending tools
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope за rejected tools
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Accessor за status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Чака одобрение',
            'approved' => 'Одобрен',
            'rejected' => 'Отказан',
            default => 'Неизвестен',
        };
    }

    /**
     * Accessor за status color (за UI)
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }
}