<?php

namespace Database\Factories;

use App\Models\DecisionList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DecisionListFactory extends Factory
{
    protected $model = DecisionList::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'is_anonymous' => $this->faker->boolean(),
            'claimed_at' => $this->faker->optional()->dateTime(),
        ];
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'is_anonymous' => true,
        ]);
    }

    public function claimed(): static
    {
        return $this->state(fn (array $attributes) => [
            'claimed_at' => $this->faker->dateTime(),
        ]);
    }
}
