<?php

namespace Database\Factories;

use App\Models\DecisionList;
use App\Models\ListParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListParticipantFactory extends Factory
{
    protected $model = ListParticipant::class;

    public function definition(): array
    {
        return [
            'list_id' => DecisionList::factory(),
            'user_id' => User::factory(),
            'share_code_id' => null,
        ];
    }
}
