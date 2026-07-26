<?php

namespace App\Http\Requests\User;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(function ($query) {
                    $companyId = $this->user()->company_id;

                    $query->where('context', 'company')
                        ->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $companyId));
                }),
            ],
        ];
    }
}
