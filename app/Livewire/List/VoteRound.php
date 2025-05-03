<?php

namespace App\Livewire\List;

use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
    public array $matchupOrder = [];

    public function mount(DecisionList $list): void
    {
        $this->list = $list;
        
        // Calculate total matchups using the formula n(n-1)/2
        $itemCount = $list->items()->count();
        $this->totalMatchups = ($itemCount * ($itemCount - 1)) / 2;
        
        // Get all pending matchups and randomize their order
        $this->matchupOrder = $list->matchups()
            ->where('status', 'pending')
            ->pluck('id')
            ->shuffle()
            ->toArray();
        
        $this->completedMatchups = $list->matchups()
            ->where('status', 'completed')
            ->count();
            
        $this->loadNextMatchup();
        $this->updateProgress();
    }

    public function loadNextMatchup(): void
    {
        // Get the next matchup ID from our randomized order
        $nextMatchupId = array_shift($this->matchupOrder);
        
        if ($nextMatchupId) {
            $this->currentMatchup = Matchup::find($nextMatchupId);
        } else {
            // If no more matchups in our order, check if there are any pending matchups
            $this->currentMatchup = $this->list->matchups()
                ->where('status', 'pending')
                ->first();
        }

        $this->updateProgress();
    }

    public function vote(int $chosenItemId): void
    {
        if (!$this->currentMatchup) {
            Log::info('No current matchup found');
            return;
        }

        // Validate that the chosen item is in the current matchup
        if (!in_array($chosenItemId, [$this->currentMatchup->item_a_id, $this->currentMatchup->item_b_id])) {
            Log::info('Invalid choice', [
                'chosen_id' => $chosenItemId,
                'item_a_id' => $this->currentMatchup->item_a_id,
                'item_b_id' => $this->currentMatchup->item_b_id
            ]);
            $this->addError('vote', 'Invalid choice');
            return;
        }

        // Check if a vote already exists for this matchup
        $existingVote = Vote::where('matchup_id', $this->currentMatchup->id)
            ->where(function ($query) {
                if (Auth::check()) {
                    $query->where('user_id', Auth::id());
                } else {
                    $query->where('session_token', session()->getId());
                }
            })
            ->first();

        if ($existingVote) {
            Log::info('Updating existing vote', [
                'vote_id' => $existingVote->id,
                'old_choice' => $existingVote->chosen_item_id,
                'new_choice' => $chosenItemId
            ]);
            // If the vote is for a different item, update it
            if ($existingVote->chosen_item_id !== $chosenItemId) {
                $existingVote->update([
                    'chosen_item_id' => $chosenItemId,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                // Update the matchup winner
                $this->currentMatchup->winner_item_id = $chosenItemId;
                $this->currentMatchup->save();
            }
        } else {
            Log::info('Creating new vote', [
                'matchup_id' => $this->currentMatchup->id,
                'user_id' => Auth::id(),
                'chosen_id' => $chosenItemId
            ]);
            // Create the vote
            Vote::create([
                'matchup_id' => $this->currentMatchup->id,
                'user_id' => Auth::id(),
                'chosen_item_id' => $chosenItemId,
                'session_token' => Auth::check() ? null : session()->getId(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Update the matchup
            Log::info('Updating matchup status', [
                'matchup_id' => $this->currentMatchup->id,
                'before_status' => $this->currentMatchup->status
            ]);
            
            $this->currentMatchup->winner_item_id = $chosenItemId;
            $this->currentMatchup->status = 'completed';
            $result = $this->currentMatchup->save();
            
            Log::info('Matchup status updated', [
                'matchup_id' => $this->currentMatchup->id,
                'after_status' => $this->currentMatchup->status,
                'save_result' => $result
            ]);

            // Update completed matchups count
            $this->completedMatchups++;
        }

        $this->updateProgress();
        
        // Load the next matchup if there are more
        if ($this->completedMatchups < $this->totalMatchups) {
            $this->loadNextMatchup();
        } else {
            // All matchups are completed, redirect to results page
            $this->redirect(route('lists.show', ['list' => $this->list]));
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