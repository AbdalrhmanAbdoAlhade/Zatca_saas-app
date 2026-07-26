<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $staffId = $this->route('staff')?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,'.$staffId],
            'phone' => ['sometimes', 'required', 'string', 'max:20', 'unique:users,phone,'.$staffId],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => [
                'sometimes',
                'required',
                Rule::exists('roles', 'id')->where('context', 'platform'),
            ],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }
}
