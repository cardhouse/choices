<?php

namespace App\Livewire\List;

use App\Models\DecisionList;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ShowList extends Component
{
    /**
     * The list to display.
     */
    public DecisionList $list;

    /**
     * The view to display.
     */
    public ?string $view = null;

    /**
     * Mount the component.
     *
     * @param  DecisionList  $list  The list to display
     * @param  string|null  $view  The view to display
     */
    public function mount(DecisionList $list, ?string $view = null): void
    {
        $this->list = $list;
        $this->view = $view;
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
        if ($this->view === 'results') {
            return view('livewire.list.show-list', [
                'results' => true
            ]);
        }

        return view('livewire.list.show-list', [
            'results' => false
        ]);
    }
}
