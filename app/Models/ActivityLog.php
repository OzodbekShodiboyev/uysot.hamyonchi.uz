<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps  = false;
    public $updatedAt   = false;

    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id',
        'description', 'old_values', 'new_values',
        'ip_address', 'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public static function log(
        string $action,
        ?Model $subject,
        string $description,
        array  $oldValues = [],
        array  $newValues = []
    ): void {
        static::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'description'  => $description,
            'old_values'   => $oldValues ?: null,
            'new_values'   => $newValues ?: null,
            'ip_address'   => request()->ip(),
            'created_at'   => now(),
        ]);
    }
}
