<?php

namespace App\Livewire\List;

use App\Models\DecisionList;
use App\Services\ScoreCalculator;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RankedResults extends Component
{
    public DecisionList $list;

    public function mount(DecisionList $list)
    {
        Gate::authorize('viewResults', $list);

        $this->list = $list;
    }

    public function render()
    {
        $calculator = app(ScoreCalculator::class);

        // Owners get the detailed analytics: voter count and the full
        // head-to-head matrix. Everyone else sees the rankings only.
        $isOwner = $this->list->isOwnedBy(auth()->user());

        return view('livewire.list.ranked-results', [
            'results' => $calculator->forList($this->list),
            'showDetails' => $isOwner,
            'voterCount' => $isOwner ? $calculator->voterCountForList($this->list) : null,
            'votingStillOpen' => $this->list->isVotingOpen(),
        ]);
    }
}
