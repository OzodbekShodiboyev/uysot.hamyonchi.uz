<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name', 'phone', 'phone_extra',
        'passport_series', 'passport_issued_date', 'passport_issued_by',
        'pinfl', 'birth_date', 'address',
        'workplace', 'position', 'notes', 'documents',
    ];

    protected $casts = [
        'birth_date'           => 'date',
        'passport_issued_date' => 'date',
        'documents'            => 'array',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class)->latest();
    }

    public function getInitialsAttribute(): string
    {
        $words = array_slice(explode(' ', $this->full_name), 0, 2);
        return implode('', array_map(
            fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)),
            $words
        ));
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }
        return $query->where(function ($q) use ($term) {
            $q->where('full_name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('passport_series', 'like', "%{$term}%")
              ->orWhere('pinfl', 'like', "%{$term}%");
        });
    }
}
