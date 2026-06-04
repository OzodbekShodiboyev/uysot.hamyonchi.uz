<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Owner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name', 'phone', 'phone_extra',
        'passport_series', 'passport_issued_date', 'passport_issued_by',
        'pinfl', 'address', 'notes', 'documents',
    ];

    protected $casts = [
        'passport_issued_date' => 'date',
        'documents'            => 'array',
    ];

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class);
    }

    public function getInitialsAttribute(): string
    {
        $words = array_slice(explode(' ', $this->full_name), 0, 2);
        return implode('', array_map(
            fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)),
            $words
        ));
    }
}
