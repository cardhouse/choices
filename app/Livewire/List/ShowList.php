<?php

namespace App\Livewire\List;

use App\Models\DecisionList;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ShowList extends Component
{
    /**
     * The list to display.
     */
    public DecisionList $list;

    /**
     * Mount the component.
     *
     * @param  DecisionList  $list  The list to display
     */
    public function mount(DecisionList $list): void
    {
        $this->list = $list;
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.list.show-list');
    }
}
