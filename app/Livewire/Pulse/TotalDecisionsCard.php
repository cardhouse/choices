<?php

namespace App\Livewire\Pulse;

use App\Models\DecisionList;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;

#[Lazy]
class TotalDecisionsCard extends Card
{
    public function render()
    {
        $totalDecisions = DecisionList::whereNotNull('voting_completed_at')->count();

        return view('livewire.pulse.total-decisions-card', [
            'totalDecisions' => $totalDecisions,
        ]);
    }
} 