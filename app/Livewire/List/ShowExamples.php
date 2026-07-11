<?php

namespace App\Livewire\List;

use App\Support\ExampleLists;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Component for displaying example decision lists to help users understand the application.
 */
#[Layout('layouts.app')]
class ShowExamples extends Component
{
    /**
     * Example lists to demonstrate the application's functionality
     */
    public array $exampleLists = [];

    /**
     * Load the shared pre-built example lists.
     */
    public function mount(): void
    {
        $this->exampleLists = ExampleLists::all();
    }

    /**
     * Start creating a new list based on an example
     */
    public function useExample(int $index)
    {
        // Store the selected example in the session and redirect to create
        session(['example_list' => $this->exampleLists[$index]]);

        return redirect()->route('lists.create');
    }

    /**
     * Start creating a new empty list
     */
    public function createNew()
    {
        return redirect()->route('lists.create');
    }

    /**
     * Render the examples view
     */
    public function render()
    {
        return view('livewire.list.show-examples');
    }
}
