<?php

namespace App\Livewire\List;

use App\Actions\Voting\CastVote;
use App\Actions\Voting\CloseVoting;
use App\Http\Requests\VoteRequest;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\Vote;
use App\Support\Voter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class VoteRound extends Component
{
    public DecisionList $list;

    public ?Matchup $currentMatchup = null;

    public int $totalMatchups = 0;

    public int $completedMatchups = 0;

    public float $progress = 0;

    /**
     * Matchup ids this voter still has to vote on, in randomized order.
     *
     * @var array<int, int>
     */
    public array $matchupQueue = [];

    /**
     * Whether this voter has voted on every matchup.
     */
    public bool $finished = false;

    public function mount(DecisionList $list)
    {
        $this->list = $list;

        if ($list->isVotingClosed()) {
            if (Auth::check() && Gate::allows('viewResults', $list)) {
                return redirect()->route('lists.results', ['list' => $list]);
            }

            session()->flash('message', 'Voting has closed on this list.');

            return redirect()->route('lists.show', ['list' => $list]);
        }

        Gate::authorize('vote', $list);

        $voter = Voter::current();

        $this->totalMatchups = $list->matchups()->count();

        $votedMatchupIds = Vote::query()
            ->byVoter($voter)
            ->whereIn('matchup_id', $list->matchups()->pluck('id'))
            ->pluck('matchup_id');

        $this->matchupQueue = $list->matchups()
            ->whereNotIn('id', $votedMatchupIds)
            ->pluck('id')
            ->shuffle()
            ->toArray();

        $this->completedMatchups = $this->totalMatchups - count($this->matchupQueue);

        $this->loadNextMatchup();
    }

    public function vote(int $chosenItemId): void
    {
        if (! $this->currentMatchup) {
            return;
        }

        $request = new VoteRequest;

        Validator::make(
            [
                'matchup_id' => $this->currentMatchup->id,
                'chosen_item_id' => $chosenItemId,
            ],
            $request->rules(),
            $request->messages(),
        )->validate();

        app(CastVote::class)->handle(
            Voter::current(),
            $this->currentMatchup,
            DecisionListItem::findOrFail($chosenItemId),
            [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
        );

        $this->completedMatchups++;
        $this->loadNextMatchup();

        if ($this->currentMatchup === null) {
            $this->finishVoting();
        }
    }

    protected function loadNextMatchup(): void
    {
        $nextMatchupId = array_shift($this->matchupQueue);

        $this->currentMatchup = $nextMatchupId ? Matchup::find($nextMatchupId) : null;
        $this->finished = $this->currentMatchup === null;

        $this->updateProgress();
    }

    /**
     * This voter has voted on every matchup; decide where they land.
     */
    protected function finishVoting(): void
    {
        // A list that was never shared has exactly one voter, so their last
        // vote concludes the decision. Shared lists stay open for the other
        // participants until the owner closes voting or the deadline passes.
        if (! $this->list->isShared()) {
            app(CloseVoting::class)->handle($this->list);
        }

        if (Auth::check()) {
            if (Gate::allows('viewResults', $this->list)) {
                $this->redirect(route('lists.results', ['list' => $this->list]));
            }

            // Participant on a still-open shared list: stay on the page and
            // show the waiting state rendered when $finished is true.
            return;
        }

        // Anonymous voters register to see results. The session id is stashed
        // because login/registration regenerates it, and the claim needs the
        // token their votes were recorded under.
        session()->put('anonymous_list_id', $this->list->id);
        session()->put('anonymous_session_token', session()->getId());
        session()->put('intended_url', route('lists.results', ['list' => $this->list]));
        session()->flash('message', 'Please register or login to view your voting results. Your votes have been saved and will be available after registration.');
        $this->redirect(route('lists.prompt', ['list' => $this->list]));
    }

    protected function updateProgress(): void
    {
        if ($this->totalMatchups > 0) {
            $this->progress = round(($this->completedMatchups / $this->totalMatchups) * 100, 2);
        }
    }

    public function render()
    {
        return view('livewire.list.vote-round');
    }
}
