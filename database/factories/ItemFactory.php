<?php

namespace Database\Factories;

use App\Models\DecisionList;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'list_id' => DecisionList::factory(),
            'label' => $this->faker->word(),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
} 