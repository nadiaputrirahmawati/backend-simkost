<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class VerifyContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // action is a route param validated by ->whereIn on the route definition.
        $action = $this->route('action');

        if ($action === 'reject') {
            return [
                'rejection_feedback' => ['required', 'string', 'max:500'],
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'rejection_feedback.required' => 'Alasan penolakan wajib diisi.',
            'rejection_feedback.max' => 'Alasan penolakan maksimal 500 karakter.',
        ];
    }
}
