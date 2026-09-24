<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejectKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejection_feedback' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_feedback.required' => 'Catatan penolakan wajib diisi.',
            'rejection_feedback.min' => 'Catatan penolakan minimal 10 karakter.',
            'rejection_feedback.max' => 'Catatan penolakan maksimal 1000 karakter.',
        ];
    }
}
