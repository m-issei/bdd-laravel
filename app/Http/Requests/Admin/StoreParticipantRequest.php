<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class StoreParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $orgId = Auth::guard('admin')->user()->organization_id;

        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => [
                'required',
                'email',
                (new Unique('participants', 'email'))
                    ->where('organization_id', $orgId)
                    ->whereNull('deleted_at'),
            ],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
