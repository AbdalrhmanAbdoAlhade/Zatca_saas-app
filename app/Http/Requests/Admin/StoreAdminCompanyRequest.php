<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company.trade_name_ar' => ['required', 'string', 'max:255'],
            'company.trade_name_en' => ['nullable', 'string', 'max:255'],
            'company.vat_number' => ['required', 'string', 'max:20', 'unique:companies,vat_number'],
            'company.commercial_registration_number' => ['nullable', 'string', 'max:50'],
            'company.country' => ['nullable', 'string', 'max:2'],
            'company.city' => ['nullable', 'string', 'max:100'],

            'owner.name' => ['required', 'string', 'max:255'],
            'owner.email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'owner.phone' => ['nullable', 'string', 'max:20'],
            'owner.password' => ['required', 'string', 'min:8'],

            'plan_id' => ['nullable', 'exists:plans,id'],
        ];
    }
}
