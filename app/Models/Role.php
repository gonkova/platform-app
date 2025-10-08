<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    // Връзка с Users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relationship: Role has many AI tools
    public function aiTools()
    {
    return $this->belongsToMany(AiTool::class, 'tool_role');
    }
}