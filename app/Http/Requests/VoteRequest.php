<?php

namespace App\Http\Requests;

use App\Rules\InMatchup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoteRequest extends FormRequest
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
            'matchup_id' => [
                'required',
                'integer',
                Rule::exists('matchups', 'id'),
            ],
            'chosen_item_id' => [
                'required',
                'integer',
                Rule::exists('items', 'id'),
                new InMatchup,
            ],
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
            'matchup_id.required' => 'A matchup must be selected.',
            'matchup_id.exists' => 'The selected matchup does not exist.',
            'chosen_item_id.required' => 'An item must be selected.',
            'chosen_item_id.exists' => 'The chosen item does not exist.',
        ];
    }
}
