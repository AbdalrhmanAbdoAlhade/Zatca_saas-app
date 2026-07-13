<?php

namespace App\Http\Requests\Zatca;

use Illuminate\Foundation\Http\FormRequest;

class ComplianceCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_base64' => ['required', 'string'],
            'invoice_hash' => ['required', 'string'],
            'uuid' => ['required', 'uuid'],
        ];
    }
}
