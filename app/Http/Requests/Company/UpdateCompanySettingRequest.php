<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'primary_color' => ['nullable', 'string', 'max:7'],
            'secondary_color' => ['nullable', 'string', 'max:7'],
            'show_qr' => ['sometimes', 'boolean'],
            'show_vat' => ['sometimes', 'boolean'],
            'show_cr' => ['sometimes', 'boolean'],
            'default_tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'invoice_pdf_language' => ['nullable', 'in:ar,en'],
        ];
    }
}
