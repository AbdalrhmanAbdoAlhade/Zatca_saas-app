<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,'.$userId],
            'phone' => ['sometimes', 'required', 'string', 'max:20', 'unique:users,phone,'.$userId],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => [
                'sometimes',
                'required',
                Rule::exists('roles', 'id')->where(function ($query) {
                    $companyId = $this->user()->company_id;

                    $query->where('context', 'company')
                        ->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $companyId));
                }),
            ],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }
}
