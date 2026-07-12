<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // تسجيل عام، متاح لأي حد
    }

    public function rules(): array
    {
        return [
            // بيانات الشركة
            'company.trade_name_ar' => ['required', 'string', 'max:255'],
            'company.trade_name_en' => ['nullable', 'string', 'max:255'],
            'company.vat_number' => ['required', 'string', 'max:20', 'unique:companies,vat_number'],
            'company.commercial_registration_number' => ['nullable', 'string', 'max:50'],
            'company.country' => ['nullable', 'string', 'size:2'],
            'company.city' => ['nullable', 'string', 'max:100'],

            // بيانات المالك (أول يوزر في الشركة)
            'owner.name' => ['required', 'string', 'max:255'],
            'owner.email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'owner.phone' => ['nullable', 'string', 'max:20'],
            'owner.password' => ['required', 'string', 'min:8', 'confirmed'],

            // اسم الجهاز عشان يتسجل مع التوكن (اختياري، لو مش مبعوت هناخد الـ User-Agent)
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
