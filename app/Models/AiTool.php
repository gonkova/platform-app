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
        'created_by',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
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

    // Relationship: Tool belongs to a user (creator)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship: Tool belongs to many categories
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'tool_category');
    }

    // Relationship: Tool belongs to many roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'tool_role');
    }
}