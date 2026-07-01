<?php

namespace App\Http\Requests\Super;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'exists:organizations,id'],
            'name'            => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email', 'unique:admins,email'],
            'password'        => ['required', 'string', 'min:8'],
        ];
    }
}
