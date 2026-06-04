<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Apartment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'block_id', 'owner_id', 'number', 'floor', 'entrance',
        'rooms', 'area_total', 'area_living', 'area_kitchen',
        'renovation', 'price_per_m2', 'total_price', 'price_podklyuch',
        'status', 'reserved_until', 'reserved_by',
        'floor_plan_image', 'floor_plan_data', 'notes',
    ];

    protected $casts = [
        'floor_plan_data'  => 'array',
        'reserved_until'   => 'datetime',
        'total_price'      => 'decimal:2',
        'price_podklyuch'  => 'decimal:2',
        'price_per_m2'     => 'decimal:2',
        'area_total'       => 'decimal:2',
        'area_living'      => 'decimal:2',
        'area_kitchen'     => 'decimal:2',
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'status_bg',
        'renovation_label',
        'formatted_price',
    ];

    // ─── Konstantlar ──────────────────────────────────────
    public const STATUSES = ['free', 'reserved', 'sold', 'unavailable'];

    public const STATUS_LABELS = [
        'free'        => "Bo'sh",
        'reserved'    => 'Band',
        'sold'        => 'Sotilgan',
        'unavailable' => 'Mavjud emas',
    ];

    public const STATUS_COLORS = [
        'free'        => '#1D9E75',
        'reserved'    => '#BA7517',
        'sold'        => '#E24B4A',
        'unavailable' => '#888780',
    ];

    public const STATUS_BG = [
        'free'        => '#E1F5EE',
        'reserved'    => '#FAEEDA',
        'sold'        => '#FCEBEB',
        'unavailable' => '#F1EFE8',
    ];

    public const RENOVATION_LABELS = [
        'none'  => "Ta'mirsiz",
        'rough' => "Qo'pol ta'mir",
        'full'  => "Tayyor ta'mir",
    ];

    // ─── Relations ────────────────────────────────────────
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function reservedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reserved_by');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class)->latest();
    }

    public function activeContract(): HasOne
    {
        return $this->hasOne(Contract::class)
            ->whereIn('status', ['draft', 'active'])
            ->latest();
    }

    // ─── Accessors ────────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? '#888780';
    }

    public function getStatusBgAttribute(): string
    {
        return self::STATUS_BG[$this->status] ?? '#F1EFE8';
    }

    public function getRenovationLabelAttribute(): string
    {
        return self::RENOVATION_LABELS[$this->renovation] ?? $this->renovation;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->total_price, 0, '.', ' ') . " so'm";
    }

    public function getFullNameAttribute(): string
    {
        return ($this->block->name ?? '') . ' · '
            . $this->floor . '-qavat · '
            . $this->number . '-xonadon';
    }

    // ─── Metodlar ─────────────────────────────────────────
    public function isFree(): bool
    {
        return $this->status === 'free';
    }

    public function isReservationExpired(): bool
    {
        return $this->status === 'reserved'
            && $this->reserved_until !== null
            && $this->reserved_until->isPast();
    }

    // ─── Scopes ───────────────────────────────────────────
    public function scopeFree(Builder $query): Builder
    {
        return $query->where('status', 'free');
    }

    public function scopeSellable(Builder $query): Builder
    {
        return $query->whereIn('status', ['free', 'reserved']);
    }

    public function scopeByBlock(Builder $query, int $blockId): Builder
    {
        return $query->where('block_id', $blockId);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['block_id']  ?? null, fn ($q, $v) => $q->where('block_id', $v))
            ->when($filters['status']    ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['rooms']     ?? null, fn ($q, $v) => $q->where('rooms', $v))
            ->when($filters['floor']     ?? null, fn ($q, $v) => $q->where('floor', $v))
            ->when($filters['min_price'] ?? null, fn ($q, $v) => $q->where('total_price', '>=', $v))
            ->when($filters['max_price'] ?? null, fn ($q, $v) => $q->where('total_price', '<=', $v));
    }
}
