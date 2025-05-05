<?php

namespace App\Livewire\Pulse;

use App\Models\Vote;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;

#[Lazy]
class TotalVotesCard extends Card
{
    public function render()
    {
        $totalVotes = Vote::count();

        return view('livewire.pulse.total-votes-card', [
            'totalVotes' => $totalVotes,
        ]);
    }
} 