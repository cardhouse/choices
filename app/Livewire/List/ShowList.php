<?php

namespace App\Livewire\List;

use App\Models\DecisionList;
use App\Models\Vote;
use App\Support\Voter;
use Illuminate\Support\Facades\Auth;
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
     * Save this list's items as a reusable template for the owner.
     */
    public function saveAsTemplate(): void
    {
        $user = Auth::user();

        if ($user === null || ! $this->list->isOwnedBy($user)) {
            abort(403);
        }

        $user->listTemplates()->create([
            'title' => $this->list->title,
            'description' => $this->list->description,
            'items' => $this->list->items()->pluck('label')->all(),
        ]);

        session()->flash('message', 'Saved to My Templates — you can re-use this list anytime.');

        $this->redirect(route('lists.show', ['list' => $this->list->id]));
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
