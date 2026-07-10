<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinListRequest extends FormRequest
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
            'code' => ['required', 'string', 'size:8', 'alpha_num:ascii'],
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
            'code.required' => 'Please enter a share code.',
            'code.size' => 'Share codes are exactly 8 characters.',
            'code.alpha_num' => 'Share codes only contain letters and numbers.',
        ];
    }
}
