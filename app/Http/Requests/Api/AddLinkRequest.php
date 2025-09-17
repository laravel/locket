<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddLinkRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url' => 'required|url|max:2048',
            'thoughts' => 'nullable|string|max:2000',
            'category_hint' => 'nullable|string|in:read,reference,watch,tools',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url.required' => 'A valid URL is required',
            'url.url' => 'Please provide a valid URL',
            'url.max' => 'The URL must not be longer than 2048 characters',
            'thoughts.max' => 'Thoughts must be less than 2000 characters',
            'category_hint.in' => 'Category must be one of: read, reference, watch, tools',
        ];
    }
}
