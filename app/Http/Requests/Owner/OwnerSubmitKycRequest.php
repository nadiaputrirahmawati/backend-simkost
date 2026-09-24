<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class OwnerSubmitKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_ktp' => ['required', 'string', 'max:20', 'unique:users,no_ktp,' . $this->user()?->id . ',id'],
            'npwp' => ['required', 'string', 'max:25'],
            'ktp_picture' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'ktp_picture_person' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
