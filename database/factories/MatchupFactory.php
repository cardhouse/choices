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
        return [
            'list_id' => DecisionList::factory(),
            'item_a_id' => DecisionListItem::factory(),
            'item_b_id' => DecisionListItem::factory(),
            'round_number' => 1,
        ];
    }
}
