<?php

namespace App\Livewire\List;

use App\Models\DecisionList;
use Livewire\Component;

/**
 * Owner analytics: full head-to-head grid where each cell shows how a row
 * item fared against a column item across all voters.
 */
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

        foreach ($items as $rowItem) {
            foreach ($items as $colItem) {
                $this->matrix[$rowItem->id][$colItem->id] = $rowItem->id === $colItem->id ? '-' : null;
            }
        }

        foreach ($matchups as $matchup) {
            $votesForA = $matchup->votes->where('chosen_item_id', $matchup->item_a_id)->count();
            $votesForB = $matchup->votes->where('chosen_item_id', $matchup->item_b_id)->count();

            if ($votesForA === 0 && $votesForB === 0) {
                continue;
            }

            $this->matrix[$matchup->item_a_id][$matchup->item_b_id] = $this->cell($votesForA, $votesForB);
            $this->matrix[$matchup->item_b_id][$matchup->item_a_id] = $this->cell($votesForB, $votesForA);
        }
    }

    /**
     * Render one cell from the row item's perspective.
     */
    protected function cell(int $ownVotes, int $opponentVotes): string
    {
        $symbol = match (true) {
            $ownVotes > $opponentVotes => '✅',
            $ownVotes < $opponentVotes => '❌',
            default => '=',
        };

        return $this->showVoteCounts ? "{$symbol} ({$ownVotes}–{$opponentVotes})" : $symbol;
    }

    public function render()
    {
        return view('livewire.list.results-matrix');
    }
}
