<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contract_id', 'payment_schedule_id', 'received_by',
        'receipt_number', 'receipt_sequence',
        'amount', 'payment_method', 'bank_reference',
        'payment_date', 'type', 'receipt_file', 'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public const METHOD_LABELS = [
        'cash'   => 'Naqd pul',
        'bank'   => "Bank o'tkazma",
        'card'   => 'Plastik karta',
        'online' => "Onlayn to'lov",
    ];

    public const TYPE_LABELS = [
        'initial'     => "Boshlang'ich to'lov",
        'installment' => "Oylik bo'lib to'lash",
        'full'        => "To'liq to'lov",
        'penalty'     => 'Jarima to\'lovi',
        'extra'       => "Qo'shimcha to'lov",
        'refund'      => 'Qaytarish',
    ];

    public static function generateReceiptNumber(int $year): string
    {
        return DB::transaction(function () use ($year) {

            $seq = DB::table('payment_sequences')
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($seq) {
                $next = $seq->last_sequence + 1;
                DB::table('payment_sequences')
                    ->where('year', $year)
                    ->update(['last_sequence' => $next]);
            } else {
                $next = 1;
                DB::table('payment_sequences')
                    ->insert(['year' => $year, 'last_sequence' => 1]);
            }

            return sprintf('KV-%d-%06d', $year, $next);
        });
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function paymentSchedule(): BelongsTo
    {
        return $this->belongsTo(PaymentSchedule::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format((float) $this->amount, 0, '.', ' ') . " so'm";
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHOD_LABELS[$this->payment_method] ?? $this->payment_method;
    }
}
