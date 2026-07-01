<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class UpdateParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $orgId         = Auth::guard('admin')->user()->organization_id;
        $participantId = $this->route('id');

        return [
            'name'  => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                (new Unique('participants', 'email'))
                    ->where('organization_id', $orgId)
                    ->whereNull('deleted_at')
                    ->ignore($participantId),
            ],
        ];
    }
}
