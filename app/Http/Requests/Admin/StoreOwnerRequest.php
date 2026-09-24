<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone_number' => ['required', 'string', 'max:20'],
            'auto_verified' => ['sometimes', 'boolean'],
            'bank_name' => ['sometimes', 'string', 'max:100'],
            'bank_account_number' => ['sometimes', 'string', 'max:50'],
            'bank_account_holder' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
