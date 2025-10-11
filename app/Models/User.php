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
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_backup_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_backup_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
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

    // ===== 2FA Methods =====

    /**
     * Проверка дали потребителят има активирана 2FA
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && !is_null($this->two_factor_secret);
    }

    /**
     * Получаване на backup codes като масив
     */
    public function getBackupCodesAttribute()
    {
        if (empty($this->two_factor_backup_codes)) {
            return [];
        }

        return json_decode(decrypt($this->two_factor_backup_codes), true) ?? [];
    }

    /**
     * Задаване на backup codes
     */
    public function setBackupCodesAttribute($value)
    {
        $this->attributes['two_factor_backup_codes'] = encrypt(json_encode($value));
    }

    /**
     * Използване на backup код
     */
    public function useBackupCode(string $code): bool
    {
        $backupCodes = $this->backup_codes;

        if (in_array($code, $backupCodes)) {
            // Премахни използвания код
            $backupCodes = array_values(array_diff($backupCodes, [$code]));
            $this->backup_codes = $backupCodes;
            $this->save();
            
            return true;
        }

        return false;
    }
}