<?php

namespace App\Livewire\List;

use App\Models\DecisionList;
use App\Services\ScoreCalculator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RankedResults extends Component
{
    public DecisionList $list;
    public bool $showVoteCounts = false;

    public function mount(DecisionList $list, bool $showVoteCounts = false)
    {
        $this->list = $list;
        $this->showVoteCounts = $showVoteCounts;
    }

    public function render()
    {
        $results = app(ScoreCalculator::class)->forList($this->list);
        
        return view('livewire.list.ranked-results', [
            'results' => $results,
        ]);
    }
} 