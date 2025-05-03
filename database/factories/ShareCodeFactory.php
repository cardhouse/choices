<?php

namespace Database\Factories;

use App\Models\DecisionList;
use App\Models\ShareCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShareCodeFactory extends Factory
{
    protected $model = ShareCode::class;

    public function definition(): array
    {
        return [
            'list_id' => DecisionList::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('????####')),
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function permanent(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }
} 