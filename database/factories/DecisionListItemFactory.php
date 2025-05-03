<?php

namespace Database\Factories;

use App\Models\DecisionList;
use App\Models\DecisionListItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class DecisionListItemFactory extends Factory
{
    protected $model = DecisionListItem::class;

    public function definition(): array
    {
        return [
            'list_id' => DecisionList::factory(),
            'label' => $this->faker->word(),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
