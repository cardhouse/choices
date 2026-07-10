<?php

namespace App\Livewire\List;

use App\Models\DecisionList;
use App\Models\Vote;
use App\Support\Voter;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ShowList extends Component
{
    /**
     * The list to display.
     */
    public DecisionList $list;

    public function mount(DecisionList $list): void
    {
        Gate::authorize('view', $list);

        $this->list = $list;
    }

    /**
     * Start voting on this list.
     */
    public function startVoting()
    {
        return redirect()->route('lists.vote', ['list' => $this->list]);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $voterVoteCount = Vote::query()
            ->byVoter(Voter::current())
            ->whereIn('matchup_id', $this->list->matchups()->pluck('id'))
            ->count();

        $totalMatchups = $this->list->matchups()->count();

        return view('livewire.list.show-list', [
            'canVote' => Gate::allows('vote', $this->list),
            'canSeeResults' => auth()->check() && Gate::allows('viewResults', $this->list),
            'canManageVoting' => auth()->check() && Gate::allows('manageVoting', $this->list),
            'voterFinished' => $totalMatchups > 0 && $voterVoteCount >= $totalMatchups,
            'voterStarted' => $voterVoteCount > 0,
        ]);
    }
}
