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
            'is_anonymous' => false,
        ];
    }

    public function anonymous(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => null,
                'is_anonymous' => true,
            ];
        });
    }

    public function claimed(): static
    {
        return $this->state(fn (array $attributes) => [
            'claimed_at' => $this->faker->dateTime(),
        ]);
    }
}
