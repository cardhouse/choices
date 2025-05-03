<?php

namespace Database\Factories;

use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use Illuminate\Database\Eloquent\Factories\Factory;

class MatchupFactory extends Factory
{
    protected $model = Matchup::class;

    public function definition(): array
    {
        return [
            'list_id' => DecisionList::factory(),
            'item_a_id' => DecisionListItem::factory(),
            'item_b_id' => DecisionListItem::factory(),
            'status' => 'pending',
            'round_number' => 1,
        ];
    }

    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'winner_item_id' => $this->faker->randomElement([$attributes['item_a_id'], $attributes['item_b_id']]),
            ];
        });
    }

    public function skipped(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'skipped',
            ];
        });
    }
}
