<?php

namespace Database\Factories;

use App\Models\ListTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListTemplateFactory extends Factory
{
    protected $model = ListTemplate::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
            'items' => $this->faker->words(4),
        ];
    }
}
