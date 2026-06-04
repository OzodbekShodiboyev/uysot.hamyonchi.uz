<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'password',
        'role', 'is_active', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    public const ROLES = [
        'admin'      => 'Admin',
        'manager'    => 'Menejer',
        'accountant' => 'Hisobchi',
        'viewer'     => "Faqat ko'rish",
    ];

    // ─── Rol tekshiruvi ───────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function isAccountant(): bool
    {
        return in_array($this->role, ['admin', 'accountant']);
    }

    public function canEdit(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'accountant']);
    }

    // ─── Relations ────────────────────────────────────────
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'manager_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ─── Accessors ────────────────────────────────────────
    public function getInitialsAttribute(): string
    {
        $words = array_slice(explode(' ', $this->name), 0, 2);
        return implode('', array_map(
            fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)),
            $words
        ));
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }
}
