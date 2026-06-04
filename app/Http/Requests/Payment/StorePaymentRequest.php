<?php
// ─── app/Http/Requests/Payment/StorePaymentRequest.php ──────────
namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAccountant() || $this->user()->isManager();
    }

    public function rules(): array
    {
        return [
            'amount'              => ['required', 'numeric', 'min:0.01'],
            'payment_method'      => ['required', 'in:cash,bank,card,online'],
            'type'                => ['required', 'in:initial,installment,full,penalty,extra,refund'],
            'payment_date'        => ['required', 'date', 'before_or_equal:today'],
            'payment_schedule_id' => ['nullable', 'integer', 'exists:payment_schedules,id'],
            'bank_reference'      => ['nullable', 'string', 'max:100'],
            'notes'               => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required'              => 'Summa kiritilmadi.',
            'amount.min'                   => "Summa 0 dan katta bo'lishi kerak.",
            'payment_method.required'      => "To'lov usuli tanlanmadi.",
            'type.required'                => "To'lov turi tanlanmadi.",
            'payment_date.required'        => 'Sana kiritilmadi.',
            'payment_date.before_or_equal' => "Sana bugundan katta bo'la olmaydi.",
        ];
    }
}
