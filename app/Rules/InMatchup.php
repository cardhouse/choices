<?php

namespace App\Rules;

use App\Models\Matchup;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class InMatchup implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! isset($this->data['matchup_id'])) {
            return;
        }

        $matchup = Matchup::find($this->data['matchup_id']);
        if (! $matchup) {
            return;
        }

        if ($value != $matchup->item_a_id && $value != $matchup->item_b_id) {
            $fail('The chosen item must be one of the items in the matchup.');
        }
    }
}
