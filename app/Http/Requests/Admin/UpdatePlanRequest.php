<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $planId = $this->route('plan')?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:100', 'unique:plans,slug,'.$planId],
            'price_monthly' => ['sometimes', 'required', 'numeric', 'min:0'],
            'price_yearly' => ['sometimes', 'required', 'numeric', 'min:0'],
            'max_users' => ['sometimes', 'required', 'integer', 'min:1'],
            'max_invoices' => ['sometimes', 'required', 'integer', 'min:1'],
            'zatca_integration' => ['sometimes', 'boolean'],
            'reports_access' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
