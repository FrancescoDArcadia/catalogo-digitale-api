<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'publication_year' => ['nullable', 'integer', 'min:100', 'max:'.date('Y')],
            'author_id' => ['sometimes', 'required', 'integer', 'exists:authors,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ];
    }
}
