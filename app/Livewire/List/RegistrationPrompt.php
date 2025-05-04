<?php

namespace App\Livewire\List;

use App\Models\DecisionList;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class RegistrationPrompt extends Component
{
    public DecisionList $list;

    public function mount(DecisionList $list): void
    {
        $this->list = $list;
    }

    public function render()
    {
        return view('livewire.list.registration-prompt');
    }
} 