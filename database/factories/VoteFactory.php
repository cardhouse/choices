<?php

namespace Database\Factories;

use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoteFactory extends Factory
{
    protected $model = Vote::class;

    public function definition(): array
    {
        $matchup = Matchup::factory()->create();
        $chosenItem = $this->faker->randomElement([$matchup->item_a_id, $matchup->item_b_id]);

        return [
            'matchup_id' => $matchup->id,
            'user_id' => User::factory(),
            'chosen_item_id' => $chosenItem,
            'session_token' => null,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }

    public function anonymous(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => null,
                'session_token' => $this->faker->uuid(),
            ];
        });
    }
}
