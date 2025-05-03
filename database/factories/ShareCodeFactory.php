<?php

namespace Database\Factories;

use App\Models\DecisionList;
use App\Models\ShareCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShareCodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ShareCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'list_id' => DecisionList::factory(),
            'code' => $this->faker->regexify('[23456789ABCDEFGHJKLMNPQRSTUVWXYZ]{8}'),
            'expires_at' => null,
            'deactivated_at' => null,
        ];
    }

    /**
     * Indicate that the code is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the code is deactivated.
     */
    public function deactivated(): static
    {
        return $this->state(fn (array $attributes) => [
            'deactivated_at' => now(),
        ]);
    }

    /**
     * Indicate that the code is permanent (no expiration).
     */
    public function permanent(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }
}
