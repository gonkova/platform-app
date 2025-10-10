<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'is_free' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Auto-generate slug from name
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tool) {
            if (empty($tool->slug)) {
                $tool->slug = Str::slug($tool->name);
            }
        });
    }

    // Relationship: AiTool belongs to User (creator)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship: AiTool belongs to User (approver)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Relationship: AiTool belongs to many Categories
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'tool_category');
    }

    // Relationship: AiTool belongs to many Roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'tool_role');
    }

    // Helper methods for status checking
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    // Helper method for difficulty level
    public function getDifficultyLevelAttribute($value)
    {
        return [
            'beginner' => 'Начинаещ',
            'intermediate' => 'Средно ниво',
            'advanced' => 'Напреднало ниво',
        ][$value] ?? $value;
    }

    // Scope for approved tools
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope for active tools
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for free tools
    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    // Scope for paid tools
    public function scopePaid($query)
    {
        return $query->where('is_free', false);
    }

    // Scope for specific difficulty
    public function scopeDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    // Scope for tools by category
    public function scopeInCategory($query, $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }

    // Scope for tools by role
    public function scopeForRole($query, $roleId)
    {
        return $query->whereHas('roles', function ($q) use ($roleId) {
            $q->where('roles.id', $roleId);
        });
    }
}