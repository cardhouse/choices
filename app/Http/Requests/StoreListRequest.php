<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreListRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:2', 'max:100'],
            'items.*' => ['required', 'string', 'min:1', 'max:255'],
            'is_anonymous' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least two items are required to create a list.',
            'items.min' => 'At least two items are required to create a list.',
            'items.max' => 'A list cannot contain more than 100 items.',
            'items.*.required' => 'Each item must not be empty.',
            'items.*.min' => 'Each item must be at least 1 character long.',
            'items.*.max' => 'Each item cannot be longer than 255 characters.',
        ];
    }
}
