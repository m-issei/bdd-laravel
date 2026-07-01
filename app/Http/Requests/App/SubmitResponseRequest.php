<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

class SubmitResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers'               => ['nullable', 'array'],
            'answers.*.question_id' => ['required', 'integer'],
            'answers.*.value'       => ['required', 'string'],
        ];
    }
}
