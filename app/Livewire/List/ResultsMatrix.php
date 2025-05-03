<?php

namespace App\Livewire\List;

use App\Models\DecisionList;
use App\Models\Matchup;
use Livewire\Component;

class ResultsMatrix extends Component
{
    public DecisionList $list;
    public array $matrix = [];
    public bool $showVoteCounts = false;

    public function mount(DecisionList $list, bool $showVoteCounts = false)
    {
        $this->list = $list;
        $this->showVoteCounts = $showVoteCounts;
        $this->buildMatrix();
    }

    protected function buildMatrix()
    {
        $items = $this->list->items()->orderBy('id')->get();
        $matchups = $this->list->matchups()->with('votes')->get();

        // Initialize matrix with empty values
        foreach ($items as $rowItem) {
            foreach ($items as $colItem) {
                if ($rowItem->id === $colItem->id) {
                    $this->matrix[$rowItem->id][$colItem->id] = '-';
                } else {
                    $this->matrix[$rowItem->id][$colItem->id] = null;
                }
            }
        }

        // Fill in the matrix with results
        foreach ($matchups as $matchup) {
            $winnerId = $matchup->winner_item_id;
            $itemAId = $matchup->item_a_id;
            $itemBId = $matchup->item_b_id;
            $voteCount = $matchup->votes->count();

            if ($winnerId) {
                $loserId = $winnerId === $itemAId ? $itemBId : $itemAId;
                $this->matrix[$winnerId][$loserId] = $this->showVoteCounts ? "✅ ($voteCount)" : "✅";
                $this->matrix[$loserId][$winnerId] = $this->showVoteCounts ? "❌ ($voteCount)" : "❌";
            } elseif ($matchup->status === 'completed') {
                $this->matrix[$itemAId][$itemBId] = $this->showVoteCounts ? "= ($voteCount)" : "=";
                $this->matrix[$itemBId][$itemAId] = $this->showVoteCounts ? "= ($voteCount)" : "=";
            }
        }
    }

    public function render()
    {
        return view('livewire.list.results-matrix');
    }
} 