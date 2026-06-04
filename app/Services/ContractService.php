<?php

namespace App\Services;

use App\Events\ApartmentStatusChanged;
use App\Models\{ActivityLog, Apartment, Contract, Payment, PaymentSchedule};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContractService
{
    /*
    |==========================================================================
    | YANGI SHARTNOMA YARATISH
    |==========================================================================
    | - lockForUpdate() bilan xonadon holati tekshiriladi
    | - Shartnoma raqami atomik ravishda generatsiya qilinadi
    | - To'lov jadvali avtomatik tuziladi
    | - Xonadon statusi 'booked' ga o'tadi
    |==========================================================================
    */
    public function create(array $data): Contract
    {
        return DB::transaction(function () use ($data) {

            // Pessimistic lock — ikki menejer bir vaqtda sota olmasin
            $apartment = Apartment::lockForUpdate()->findOrFail($data['apartment_id']);

            if (!in_array($apartment->status, ['free', 'reserved'])) {
                throw new \RuntimeException(
                    "Xonadon №{$apartment->number} hozir sotib bo'lmaydi. "
                    . "Joriy holati: {$apartment->status_label}"
                );
            }

            $year    = now()->year;
            $blockId = $apartment->block_id;

            // Shartnoma raqami (pessimistic lock ichida)
            $contractNumber = Contract::generateNumber($blockId, $year);

            // Summa hisob-kitobi
            $totalPrice      = (float) $data['total_price'];
            $discountAmount  = (float) ($data['discount_amount'] ?? 0);
            $finalPrice      = $totalPrice - $discountAmount;
            $initialPayment  = (float) ($data['initial_payment'] ?? 0);
            $initialPercent  = $finalPrice > 0
                ? round(($initialPayment / $finalPrice) * 100, 2) : 0;
            $remainingAmount = max(0, $finalPrice - $initialPayment);

            $installmentMonths = 0;
            $monthlyPayment    = 0;
            $installStart      = null;
            $installEnd        = null;

            if ($data['payment_type'] === 'installment') {
                $installmentMonths = (int) ($data['installment_months'] ?? 0);
                $monthlyPayment    = $installmentMonths > 0
                    ? round($remainingAmount / $installmentMonths, 2) : 0;
                $installStart = isset($data['installment_start_date'])
                    ? Carbon::parse($data['installment_start_date'])
                    : Carbon::now()->addMonth()->startOfMonth();
                $installEnd = $installStart->copy()->addMonths($installmentMonths - 1);
            }

            // Tartib raqamini chiqarib olish (masalan: "2024/B1-0007" → 7)
            $seqNum = (int) substr($contractNumber, strrpos($contractNumber, '-') + 1);

            // Shartnomani saqlash
            $contract = Contract::create([
                'contract_number'        => $contractNumber,
                'sequence_number'        => $seqNum,
                'contract_year'          => $year,
                'apartment_id'           => $apartment->id,
                'client_id'              => $data['client_id'],
                'manager_id'             => $data['manager_id'] ?? auth()->id(),
                'accountant_id'          => $data['accountant_id'] ?? null,
                'payment_type'           => $data['payment_type'],
                'total_price'            => $totalPrice,
                'discount_amount'        => $discountAmount,
                'final_price'            => $finalPrice,
                'initial_payment'        => $initialPayment,
                'initial_percent'        => $initialPercent,
                'remaining_amount'       => $remainingAmount,
                'paid_amount'            => 0,
                'debt_amount'            => $finalPrice,
                'installment_months'     => $installmentMonths,
                'monthly_payment'        => $monthlyPayment,
                'installment_start_date' => $installStart?->toDateString(),
                'installment_end_date'   => $installEnd?->toDateString(),
                'status'                 => 'draft',
                'signed_date'            => $data['signed_date'] ?? null,
                'expected_handover_date' => $data['expected_handover_date'] ?? null,
                'notes'                  => $data['notes'] ?? null,
            ]);

            // Xonadon holatini yangilash: band
            $apartment->update([
                'status'         => 'reserved',
                'reserved_until' => null,
                'reserved_by'    => null,
            ]);

            // Bo'lib to'lash jadvali
            if ($data['payment_type'] === 'installment') {
                $contract->generatePaymentSchedule();
            }

            // Log
            ActivityLog::log(
                'contract.created',
                $contract,
                "Shartnoma №{$contractNumber} yaratildi — {$apartment->full_name}",
                [],
                [
                    'contract_number' => $contractNumber,
                    'payment_type'    => $data['payment_type'],
                    'final_price'     => $finalPrice,
                ]
            );

            // Real-time broadcast
            broadcast(new ApartmentStatusChanged($apartment))->toOthers();

            return $contract->load([
                'apartment.block', 'client', 'manager', 'paymentSchedules',
            ]);
        });
    }

    /*
    |==========================================================================
    | SHARTNOMANI FAOLLASHTIRISH (draft → active)
    |==========================================================================
    */
    public function activate(Contract $contract): Contract
    {
        return DB::transaction(function () use ($contract) {
            if ($contract->status !== 'draft') {
                throw new \RuntimeException(
                    'Faqat loyiha (draft) statusidagi shartnoma faollashtiriladi.'
                );
            }

            $contract->update([
                'status'      => 'active',
                'signed_date' => $contract->signed_date ?? now()->toDateString(),
            ]);

            $contract->apartment->update(['status' => 'sold']);

            ActivityLog::log(
                'contract.activated',
                $contract,
                "Shartnoma №{$contract->contract_number} faollashtirildi"
            );

            broadcast(new ApartmentStatusChanged($contract->apartment->fresh()))->toOthers();

            return $contract->fresh();
        });
    }

    /*
    |==========================================================================
    | TO'LOV QABUL QILISH
    |==========================================================================
    | - Kvitansiya raqami atomik ravishda generatsiya qilinadi (KV-2024-000001)
    | - Jadval holati yangilanadi
    | - Shartnoma jami summasi qayta hisoblanadi
    |==========================================================================
    */
    public function receivePayment(Contract $contract, array $data): Payment
    {
        return DB::transaction(function () use ($contract, $data) {
            $year          = now()->year;
            $receiptNumber = Payment::generateReceiptNumber($year);
            $seqNum        = (int) substr($receiptNumber, strrpos($receiptNumber, '-') + 1);

            $payment = Payment::create([
                'contract_id'         => $contract->id,
                'payment_schedule_id' => $data['payment_schedule_id'] ?? null,
                'received_by'         => auth()->id(),
                'receipt_number'      => $receiptNumber,
                'receipt_sequence'    => $seqNum,
                'amount'              => $data['amount'],
                'payment_method'      => $data['payment_method'] ?? 'cash',
                'bank_reference'      => $data['bank_reference'] ?? null,
                'payment_date'        => $data['payment_date'] ?? now()->toDateString(),
                'type'                => $data['type'],
                'notes'               => $data['notes'] ?? null,
            ]);

            // Jadval holatini yangilash
            if (!empty($data['payment_schedule_id'])) {
                $schedule = PaymentSchedule::find($data['payment_schedule_id']);
                if ($schedule) {
                    $this->updateScheduleAfterPayment($schedule, (float) $data['amount']);
                }
            }

            // Muddati o'tgan jadvallarni belgilash
            $this->markOverdueSchedules($contract);

            // Shartnoma summalarini qayta hisoblash
            $contract->recalculate();

            ActivityLog::log(
                'payment.received',
                $payment,
                "To'lov qabul qilindi: {$payment->formatted_amount} — {$receiptNumber}",
                [],
                [
                    'amount'  => $data['amount'],
                    'type'    => $data['type'],
                    'method'  => $data['payment_method'] ?? 'cash',
                    'receipt' => $receiptNumber,
                ]
            );

            return $payment->load(['contract.client', 'receivedBy']);
        });
    }

    /*
    |==========================================================================
    | VAQTINCHALIK BAND QILISH
    |==========================================================================
    */
    public function reserve(Apartment $apartment, int $userId, int $hours = 24): void
    {
        DB::transaction(function () use ($apartment, $userId, $hours) {

            $fresh = Apartment::lockForUpdate()->findOrFail($apartment->id);

            if ($fresh->status !== 'free') {
                throw new \RuntimeException(
                    "Xonadon №{$fresh->number} allaqachon band. "
                    . "Holati: {$fresh->status_label}"
                );
            }

            $until = now()->addHours($hours);

            $fresh->update([
                'status'         => 'reserved',
                'reserved_until' => $until,
                'reserved_by'    => $userId,
            ]);

            ActivityLog::log(
                'apartment.reserved',
                $fresh,
                "Xonadon №{$fresh->number} {$hours} soatga band qilindi",
                ['status' => 'free'],
                ['status' => 'reserved', 'until' => $until->toDateTimeString()]
            );

            broadcast(new ApartmentStatusChanged($fresh))->toOthers();
        });
    }

    /*
    |==========================================================================
    | BANDLIKNI BEKOR QILISH
    |==========================================================================
    */
    public function release(Apartment $apartment): void
    {
        DB::transaction(function () use ($apartment) {

            if ($apartment->activeContract()->exists()) {
                throw new \RuntimeException(
                    "Bu xonadon bo'yicha faol shartnoma mavjud. "
                    . 'Avval shartnomani bekor qiling.'
                );
            }

            $old = $apartment->status;

            $apartment->update([
                'status'         => 'free',
                'reserved_until' => null,
                'reserved_by'    => null,
            ]);

            ActivityLog::log(
                'apartment.released',
                $apartment,
                "Xonadon №{$apartment->number} bo'shatildi",
                ['status' => $old],
                ['status' => 'free']
            );

            broadcast(new ApartmentStatusChanged($apartment))->toOthers();
        });
    }

    /*
    |==========================================================================
    | SHARTNOMANI BEKOR QILISH
    |==========================================================================
    */
    public function cancel(Contract $contract, string $reason): void
    {
        DB::transaction(function () use ($contract, $reason) {

            if ((float) $contract->paid_amount > 0) {
                throw new \RuntimeException(
                    "To'lov amalga oshirilgan shartnomani bekor qilish uchun "
                    . 'avval hisobchiga murojaat qiling.'
                );
            }

            $contract->update([
                'status'         => 'cancelled',
                'cancelled_date' => now()->toDateString(),
                'cancel_reason'  => $reason,
            ]);

            $contract->apartment->update([
                'status'         => 'free',
                'reserved_until' => null,
                'reserved_by'    => null,
            ]);

            ActivityLog::log(
                'contract.cancelled',
                $contract,
                "Shartnoma №{$contract->contract_number} bekor qilindi. Sabab: {$reason}"
            );

            broadcast(new ApartmentStatusChanged($contract->apartment))->toOthers();
        });
    }

    // ─── Private helpers ──────────────────────────────────
    private function updateScheduleAfterPayment(PaymentSchedule $schedule, float $amount): void
    {
        $total = (float) $schedule->paid_amount + $amount;

        $schedule->update([
            'paid_amount' => $total,
            'paid_date'   => now()->toDateString(),
            'status'      => $total >= (float) $schedule->amount ? 'paid' : 'partial',
        ]);
    }

    private function markOverdueSchedules(Contract $contract): void
    {
        $contract->paymentSchedules()
            ->whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);
    }
}
