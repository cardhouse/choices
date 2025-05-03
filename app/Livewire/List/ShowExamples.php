<?php

namespace App\Livewire\List;

use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Component for displaying example decision lists to help users understand the application.
 */
#[Layout('layouts.app')]
class ShowExamples extends Component
{
    /**
     * Example lists to demonstrate the application's functionality
     */
    public array $exampleLists = [
        [
            'title' => 'Dinner Menu Planning',
            'description' => 'Deciding on next week\'s dinner menu',
            'items' => ['Pizza', 'Sushi', 'Tacos', 'Pasta', 'Salad', 'Burgers'],
        ],
        [
            'title' => 'Weekend Activity',
            'description' => 'What should we do this weekend?',
            'items' => ['Movie Night', 'Hiking', 'Board Games', 'Beach Trip', 'Shopping'],
        ],
        [
            'title' => 'Project Priorities',
            'description' => 'Ranking features for next sprint',
            'items' => ['User Authentication', 'Payment Integration', 'Search Feature', 'Analytics Dashboard'],
        ],
    ];

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