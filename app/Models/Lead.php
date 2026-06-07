<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'full_name', 'phone', 'phone_extra',
        'source', 'status',
        'interested_block_id', 'rooms', 'budget',
        'notes', 'next_follow_up',
        'assigned_to', 'created_by',
        'converted_client_id',
    ];

    protected $casts = [
        'next_follow_up' => 'date',
        'budget'         => 'decimal:0',
    ];

    // ── Statik ma'lumotlar ─────────────────────────────

    public static function statuses(): array
    {
        return [
            'new'          => ['label' => 'Yangi',        'color' => '#3b82f6', 'bg' => '#eff6ff', 'border' => '#bfdbfe'],
            'contacted'    => ['label' => "Bog'lanildi",  'color' => '#8b5cf6', 'bg' => '#f5f3ff', 'border' => '#ddd6fe'],
            'consultation' => ['label' => 'Maslahat',     'color' => '#f59e0b', 'bg' => '#fffbeb', 'border' => '#fde68a'],
            'considering'  => ['label' => "O'ylayapti",   'color' => '#0ea5e9', 'bg' => '#f0f9ff', 'border' => '#bae6fd'],
            'hot'          => ['label' => 'Issiq',        'color' => '#ef4444', 'bg' => '#fef2f2', 'border' => '#fecaca'],
            'lost'         => ['label' => 'Rad etdi',     'color' => '#6b7280', 'bg' => '#f9fafb', 'border' => '#e5e7eb'],
            'converted'    => ['label' => 'Shartnoma',    'color' => '#10b981', 'bg' => '#ecfdf5', 'border' => '#a7f3d0'],
        ];
    }

    public static function sources(): array
    {
        return [
            'instagram' => ['label' => 'Instagram',       'icon' => 'fa-instagram'],
            'telegram'  => ['label' => 'Telegram',        'icon' => 'fa-telegram'],
            'phone'     => ['label' => 'Telefon',         'icon' => 'fa-phone'],
            'referral'  => ['label' => "Do'st/tanish",    'icon' => 'fa-user-group'],
            'office'    => ['label' => 'Ofisga keldi',    'icon' => 'fa-building'],
            'banner'    => ['label' => 'Banner/reklama',  'icon' => 'fa-rectangle-ad'],
            'other'     => ['label' => 'Boshqa',          'icon' => 'fa-circle-question'],
        ];
    }

    // ── Accessorlar ────────────────────────────────────

    public function getStatusInfoAttribute(): array
    {
        return static::statuses()[$this->status] ?? static::statuses()['new'];
    }

    public function getSourceInfoAttribute(): array
    {
        return static::sources()[$this->source] ?? static::sources()['other'];
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->next_follow_up && $this->next_follow_up->isPast()
            && !in_array($this->status, ['lost', 'converted']);
    }

    // ── Relationlar ────────────────────────────────────

    public function interestedBlock(): BelongsTo
    {
        return $this->belongsTo(Block::class, 'interested_block_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function convertedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }

    // ── Scope ──────────────────────────────────────────

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;
        return $query->where(function ($q) use ($term) {
            $q->where('full_name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if (!$status || $status === 'all') return $query;
        return $query->where('status', $status);
    }
}
