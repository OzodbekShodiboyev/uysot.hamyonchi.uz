<?php

namespace App\Http\Requests\Apartment;

class UpdateApartmentRequest extends StoreApartmentRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}
