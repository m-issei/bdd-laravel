<?php

namespace App\Http\Requests\Super;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminAccountRequest extends FormRequest
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
            'email'           => ['required', 'email', Rule::unique('admins', 'email')->ignore($this->route('admin'))],
        ];
    }
}
