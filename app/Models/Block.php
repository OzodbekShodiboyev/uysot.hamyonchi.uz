<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    protected $fillable = [
        'name', 'code', 'address', 'total_floors',
        'color', 'description', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'total_floors' => 'integer',
        'sort_order'   => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────
    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class)
            ->orderBy('floor')
            ->orderBy('number');
    }

    public function sequences(): HasMany
    {
        return $this->hasMany(ContractSequence::class);
    }

    // ─── Blok statistikasi ────────────────────────────────
    public function stats(): array
    {
        $raw = $this->apartments()
            ->selectRaw('status, COUNT(*) as cnt, SUM(total_price) as total_val')
            ->reorder()
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $total = $raw->sum('cnt');

        return [
            'free'        => (int) ($raw['free']->cnt ?? 0),
            'reserved'    => (int) ($raw['reserved']->cnt ?? 0),
            'sold'        => (int) ($raw['sold']->cnt ?? 0),
            'unavailable' => (int) ($raw['unavailable']->cnt ?? 0),
            'total'       => (int) $total,
            'total_value' => (float) $raw->sum('total_val'),
        ];
    }

    // Qavatlar ro'yxati (yuqoridan pastga)
    public function floorNumbers(): array
    {
        return $this->apartments()
            ->distinct()
            ->orderByDesc('floor')
            ->pluck('floor')
            ->toArray();
    }

    // ─── Scopes ───────────────────────────────────────────
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
