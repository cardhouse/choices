<?php

namespace Database\Factories;

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
            'session_token' => $this->faker->optional()->uuid(),
            'chosen_item_id' => $chosenItem,
            'ip_address' => $this->faker->optional()->ipv4(),
            'user_agent' => $this->faker->optional()->userAgent(),
        ];
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'session_token' => $this->faker->uuid(),
        ]);
    }
}
