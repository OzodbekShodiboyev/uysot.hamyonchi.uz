<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contract_number', 'sequence_number', 'contract_year',
        'apartment_id', 'client_id', 'manager_id', 'accountant_id',
        'payment_type',
        'total_price', 'discount_amount', 'final_price',
        'initial_payment', 'initial_percent', 'remaining_amount',
        'paid_amount', 'debt_amount',
        'installment_months', 'monthly_payment',
        'installment_start_date', 'installment_end_date',
        'status', 'signed_date',
        'expected_handover_date', 'actual_handover_date',
        'cancelled_date', 'cancel_reason',
        'contract_file', 'notes', 'metadata',
    ];

    protected $casts = [
        'signed_date'            => 'date',
        'installment_start_date' => 'date',
        'installment_end_date'   => 'date',
        'expected_handover_date' => 'date',
        'actual_handover_date'   => 'date',
        'cancelled_date'         => 'date',
        'total_price'            => 'decimal:2',
        'discount_amount'        => 'decimal:2',
        'final_price'            => 'decimal:2',
        'initial_payment'        => 'decimal:2',
        'initial_percent'        => 'decimal:2',
        'remaining_amount'       => 'decimal:2',
        'paid_amount'            => 'decimal:2',
        'debt_amount'            => 'decimal:2',
        'monthly_payment'        => 'decimal:2',
        'metadata'               => 'array',
    ];

    /*
    |==========================================================================
    | SHARTNOMA RAQAMI GENERATSIYASI
    | Format: 2024/B1-0001
    |   YIL / BLOK_KODI - TARTIB(4 raqam, 0001 dan boshlanadi)
    |
    | Mexanizm:
    |   - contract_sequences jadvalida blok+yil bo'yicha counter
    |   - lockForUpdate() — bir vaqtda 2 menejer bir raqamni ola olmaydi
    |   - Har yangi yilda har blok uchun qayta 0001 dan boshlanadi
    |==========================================================================
    */
    public static function generateNumber(int $blockId, int $year): string
    {
        return DB::transaction(function () use ($blockId, $year) {

            $seq = DB::table('contract_sequences')
                ->where('block_id', $blockId)
                ->where('year', $year)
                ->lockForUpdate()   // Pessimistic lock
                ->first();

            if ($seq) {
                $next = $seq->last_sequence + 1;
                DB::table('contract_sequences')
                    ->where('id', $seq->id)
                    ->update(['last_sequence' => $next]);
            } else {
                $next = 1;
                DB::table('contract_sequences')->insert([
                    'block_id'      => $blockId,
                    'year'          => $year,
                    'last_sequence' => 1,
                ]);
            }

            $blockCode = Block::find($blockId)?->code ?? 'XX';

            // Format: 2024/B1-0001
            return sprintf('%d/%s-%04d', $year, $blockCode, $next);
        });
    }

    /*
    |==========================================================================
    | TO'LOV JADVALI GENERATSIYASI
    | - Har oyning 10-sanasida to'lov
    | - Oxirgi oy: yaxlitlanmagan qoldiq summa
    |==========================================================================
    */
    public function generatePaymentSchedule(): void
    {
        if ($this->payment_type === 'full') {
            return;
        }

        $this->paymentSchedules()->delete();

        $start     = $this->installment_start_date
            ?? Carbon::now()->addMonth()->startOfMonth();
        $monthly   = (float) $this->monthly_payment;
        $remaining = (float) $this->remaining_amount;

        for ($i = 1; $i <= $this->installment_months; $i++) {
            $isLast = ($i === $this->installment_months);
            $amount = $isLast ? round($remaining, 2) : $monthly;

            $this->paymentSchedules()->create([
                'payment_number' => $i,
                'total_payments' => $this->installment_months,
                'amount'         => $amount,
                'due_date'       => $start->copy()->addMonths($i - 1)->toDateString(),
                'status'         => 'pending',
            ]);

            $remaining -= $monthly;
        }
    }

    /*
    |==========================================================================
    | SUMMALARNI QAYTA HISOBLASH
    | Har to'lovdan keyin chaqiriladi
    |==========================================================================
    */
    public function recalculate(): void
    {
        $paid     = (float) $this->payments()->where('type', '!=', 'refund')->sum('amount');
        $refunded = (float) $this->payments()->where('type', 'refund')->sum('amount');
        $net      = max(0, $paid - $refunded);
        $debt     = max(0, (float) $this->final_price - $net);

        $this->update([
            'paid_amount' => $net,
            'debt_amount' => $debt,
        ]);

        // Qarz to'liq to'langan bo'lsa — yakunlash
        if ($debt <= 0 && $this->status === 'active') {
            $this->update(['status' => 'completed']);
            $this->apartment->update(['status' => 'sold']);
        }
    }

    // ─── Relations ────────────────────────────────────────
    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function accountant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accountant_id');
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class)->orderBy('payment_number');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('payment_date');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'subject_id')
            ->where('subject_type', self::class)
            ->orderByDesc('created_at');
    }

    // ─── Accessors ────────────────────────────────────────
    public function getPaymentTypeLabelAttribute(): string
    {
        return match ($this->payment_type) {
            'full'        => "100% to'liq to'lov",
            'installment' => "Bo'lib to'lash ({$this->installment_months} oy)",
            default       => $this->payment_type,
        };
    }

    public function getPaymentTypeBadgeAttribute(): string
    {
        return match ($this->payment_type) {
            'full'        => '100%',
            'installment' => $this->installment_months . ' oy',
            default       => '—',
        };
    }

    public function getProgressPercentAttribute(): float
    {
        if ((float) $this->final_price <= 0) {
            return 0;
        }
        return min(100, round(((float) $this->paid_amount / (float) $this->final_price) * 100, 1));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Loyiha',
            'active'    => 'Faol',
            'completed' => 'Yakunlangan',
            'cancelled' => 'Bekor qilingan',
            'disputed'  => 'Muammo bor',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'     => '#BA7517',
            'active'    => '#185FA5',
            'completed' => '#1D9E75',
            'cancelled', 'disputed' => '#E24B4A',
            default     => '#888780',
        };
    }

    public function getNextDueScheduleAttribute(): ?PaymentSchedule
    {
        return $this->paymentSchedules
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sortBy('due_date')
            ->first();
    }

    public function getOverdueCountAttribute(): int
    {
        return $this->paymentSchedules->where('status', 'overdue')->count();
    }

    // ─── Scopes ───────────────────────────────────────────
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeHasDebt(Builder $query): Builder
    {
        return $query->where('debt_amount', '>', 0);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }
        return $query->where(function ($q) use ($term) {
            $q->where('contract_number', 'like', "%{$term}%")
              ->orWhereHas('client', fn ($c) =>
                  $c->where('full_name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('passport_series', 'like', "%{$term}%")
              );
        });
    }
}
