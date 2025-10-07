<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Връзка с Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Помощен метод за проверка на роля
    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }

    // Проверка дали е Owner
    public function isOwner()
    {
        return $this->hasRole('owner');
    }
}