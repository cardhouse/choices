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
        $list = DecisionList::factory()->create();
        $itemA = DecisionListItem::factory()->create(['list_id' => $list->id]);
        $itemB = DecisionListItem::factory()->create(['list_id' => $list->id]);

        return [
            'list_id' => $list->id,
            'item_a_id' => $itemA->id,
            'item_b_id' => $itemB->id,
            'winner_item_id' => null,
            'status' => $this->faker->randomElement(['pending', 'completed', 'skipped']),
            'round_number' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $winner = $this->faker->randomElement([$attributes['item_a_id'], $attributes['item_b_id']]);

            return [
                'status' => 'completed',
                'winner_item_id' => $winner,
            ];
        });
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'winner_item_id' => null,
        ]);
    }
}
