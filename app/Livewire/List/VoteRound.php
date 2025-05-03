<?php

namespace App\Livewire\List;

use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    public function mount(DecisionList $list): void
    {
        $this->list = $list;
        
        // Calculate total matchups using the formula n(n-1)/2
        $itemCount = $list->items()->count();
        $this->totalMatchups = ($itemCount * ($itemCount - 1)) / 2;
        
        $this->completedMatchups = $list->matchups()
            ->where('status', 'completed')
            ->count();
            
        $this->loadNextMatchup();
        $this->updateProgress();
    }

    public function loadNextMatchup(): void
    {
        $this->currentMatchup = $this->list->matchups()
            ->where('status', 'pending')
            ->first();

        $this->updateProgress();
    }

    public function vote(int $chosenItemId): void
    {
        if (!$this->currentMatchup) {
            return;
        }

        // Validate that the chosen item is in the current matchup
        if (!in_array($chosenItemId, [$this->currentMatchup->item_a_id, $this->currentMatchup->item_b_id])) {
            $this->addError('vote', 'Invalid choice');
            return;
        }

        // Create the vote
        Vote::create([
            'matchup_id' => $this->currentMatchup->id,
            'user_id' => Auth::id(),
            'chosen_item_id' => $chosenItemId,
            'session_token' => Auth::check() ? null : Str::uuid(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Update the matchup
        $this->currentMatchup->update([
            'winner_item_id' => $chosenItemId,
            'status' => 'completed',
        ]);

        // Update completed matchups count and progress
        $this->completedMatchups++;
        $this->updateProgress();
        
        // Load the next matchup if there are more
        if ($this->completedMatchups < $this->totalMatchups) {
            $this->loadNextMatchup();
        } else {
            $this->currentMatchup = null;
        }
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