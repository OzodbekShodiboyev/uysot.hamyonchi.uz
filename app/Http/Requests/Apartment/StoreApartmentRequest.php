<?php

namespace App\Http\Requests\Apartment;

use Illuminate\Foundation\Http\FormRequest;

class StoreApartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'block_id'     => ['required', 'integer', 'exists:blocks,id'],
            'owner_id'     => ['nullable', 'integer', 'exists:owners,id'],
            'number'       => ['required', 'string', 'max:20'],
            'floor'        => ['required', 'integer', 'min:1', 'max:50'],
            'entrance'     => ['nullable', 'integer', 'min:1', 'max:20'],
            'rooms'        => ['required', 'integer', 'min:1', 'max:10'],
            'area_total'   => ['required', 'numeric', 'min:10', 'max:1000'],
            'area_living'  => ['nullable', 'numeric', 'min:1'],
            'area_kitchen' => ['nullable', 'numeric', 'min:1'],
            'renovation'   => ['nullable', 'in:none,rough,full'],
            'price_per_m2'         => ['nullable', 'numeric', 'min:0'],
            'total_price'          => ['required', 'numeric', 'min:1000'],
            'price_podklyuch'      => ['nullable', 'numeric', 'min:1000'],
            'price_karobka_full'   => ['nullable', 'numeric', 'min:1000'],
            'price_podklyuch_full' => ['nullable', 'numeric', 'min:1000'],
            'notes'                => ['nullable', 'string', 'max:2000'],
        ];
    }
}
