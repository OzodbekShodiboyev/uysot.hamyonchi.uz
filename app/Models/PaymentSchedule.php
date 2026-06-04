<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentSchedule extends Model
{
    protected $fillable = [
        'contract_id', 'payment_number', 'total_payments',
        'amount', 'penalty_rate', 'due_date', 'status',
        'paid_amount', 'penalty_amount', 'paid_date', 'notes',
    ];

    protected $casts = [
        'due_date'       => 'date',
        'paid_date'      => 'date',
        'amount'         => 'decimal:2',
        'paid_amount'    => 'decimal:2',
        'penalty_amount' => 'decimal:2',
    ];

    public const STATUS_LABELS = [
        'pending'   => 'Kutilmoqda',
        'paid'      => "To'langan",
        'partial'   => 'Qisman to\'langan',
        'overdue'   => "Muddati o'tgan",
        'cancelled' => 'Bekor qilingan',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, ['pending', 'partial'])
            && $this->due_date->isPast();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return (int) now()->diffInDays($this->due_date);
    }

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isOverdue() && $this->status !== 'paid') {
            return "Muddati o'tgan ({$this->days_overdue} kun)";
        }
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
