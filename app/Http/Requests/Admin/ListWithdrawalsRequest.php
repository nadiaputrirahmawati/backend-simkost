<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ListWithdrawalsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'nullable', 'string', 'in:pending,approved,rejected'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status harus salah satu dari: pending, approved, rejected.',
            'per_page.integer' => 'Per page harus berupa angka.',
            'per_page.min' => 'Per page minimal 1.',
            'per_page.max' => 'Per page maksimal 100.',
        ];
    }
}
