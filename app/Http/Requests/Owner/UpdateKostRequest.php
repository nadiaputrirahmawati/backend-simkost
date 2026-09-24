<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'type'            => ['sometimes', 'required', 'in:campur,putri,putra'],
            'address'         => ['sometimes', 'required', 'string'],
            'city'            => ['sometimes', 'required', 'string', 'max:100'],
            'latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'numeric', 'between:-180,180'],
            'description'     => ['nullable', 'string'],
            'public_facility' => ['nullable', 'array'],
            'public_facility.*' => ['string', 'max:255'],
            'regulation'      => ['nullable', 'array'],
            'regulation.*'    => ['string', 'max:255'],
        ];
    }
}
