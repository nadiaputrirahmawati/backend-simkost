<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_number'    => ['required', 'string', 'max:50'],
            'room_size'      => ['nullable', 'string', 'max:20'],
            'price'          => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'status'         => ['sometimes', 'in:available,occupied,maintenance'],
            'room_facility'  => ['nullable', 'array'],
            'room_facility.*' => ['string', 'max:255'],
        ];
    }
}
