<?php
// ─── app/Http/Requests/Contract/StoreContractRequest.php ─────────
namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isManager();
    }

    public function rules(): array
    {
        return [
            'apartment_id'           => ['required', 'integer', 'exists:apartments,id'],
            'client_id'              => ['required', 'integer', 'exists:clients,id'],
            'manager_id'             => ['nullable', 'integer', 'exists:users,id'],
            'accountant_id'          => ['nullable', 'integer', 'exists:users,id'],
            'payment_type'           => ['required', 'in:full,installment'],
            'total_price'            => ['required', 'numeric', 'min:1'],
            'discount_amount'        => ['nullable', 'numeric', 'min:0'],
            'initial_payment'        => ['nullable', 'numeric', 'min:0'],
            'installment_months'     => ['required_if:payment_type,installment', 'nullable', 'integer', 'min:1', 'max:360', 'exclude_if:payment_type,full'],
            'installment_start_date' => ['nullable', 'date'],
            'signed_date'            => ['nullable', 'date'],
            'expected_handover_date' => ['nullable', 'date', 'after_or_equal:signed_date'],
            'notes'                  => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'apartment_id.required'              => 'Xonadon tanlanmadi.',
            'client_id.required'                 => 'Mijoz tanlanmadi.',
            'payment_type.required'              => "To'lov turi tanlanmadi.",
            'total_price.required'               => 'Narx kiritilmadi.',
            'installment_months.required_if'     => "Bo'lib to'lash muddati kiritilmadi.",
            'expected_handover_date.after_or_equal' => 'Topshirish sanasi imzolash sanasidan keyin bo\'lishi kerak.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Agar manager_id bo'sh bo'lsa, joriy foydalanuvchi
        if (empty($this->manager_id)) {
            $this->merge(['manager_id' => auth()->id()]);
        }
        // discount_amount bo'sh bo'lsa 0
        if (empty($this->discount_amount)) {
            $this->merge(['discount_amount' => 0]);
        }
        // initial_payment bo'sh bo'lsa 0
        if (empty($this->initial_payment)) {
            $this->merge(['initial_payment' => 0]);
        }
    }
}
