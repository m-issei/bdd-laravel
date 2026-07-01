<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text'              => ['required', 'string', 'max:500'],
            'answer_type_id'    => ['required', 'integer', 'exists:answer_types,id'],
            'survey_section_id' => ['nullable', 'integer'],
            'order'             => ['nullable', 'integer'],
        ];
    }
}
