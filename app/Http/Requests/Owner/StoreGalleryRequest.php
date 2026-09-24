<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image'     => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'room_id'   => ['nullable', 'string', 'exists:rooms,id'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
