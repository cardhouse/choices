<?php

namespace App\Livewire\List;

use App\Actions\Lists\CreateList as CreateListAction;
use App\Http\Requests\StoreListRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateList extends Component
{
    /**
     * The list title.
     */
    public string $title = '';

    /**
     * The list description.
     */
    public string $description = '';

    /**
     * The list items.
     *
     * @var array<int, string>
     */
    public array $items = ['', ''];

    /**
     * Mount the component and initialize with example if provided
     */
    public function mount(): void
    {
        if ($example = session('example_list')) {
            $this->title = $example['title'];
            $this->description = $example['description'];
            $this->items = $example['items'];
            session()->forget('example_list');
        }
    }

    /**
     * Create the list and its items.
     */
    public function createList(): void
    {
        $request = new StoreListRequest;

        $validated = Validator::make(
            [
                'title' => $this->title,
                'description' => $this->description ?: null,
                'items' => array_values(array_filter($this->items, fn ($item) => trim($item) !== '')),
            ],
            $request->rules(),
            $request->messages(),
        )->validate();

        try {
            $list = app(CreateListAction::class)->handle($validated, Auth::user());
        } catch (\Exception $e) {
            Log::error('Failed to create list: '.$e->getMessage());
            $this->addError('title', 'Failed to create list. Please try again.');

            return;
        }

        $this->redirect(route('lists.show', ['list' => $list->id]));
    }

    /**
     * Add a new item.
     */
    public function addItem(): void
    {
        if (count($this->items) < 100) {
            $this->items[] = '';
        }
    }

    /**
     * Remove an item.
     */
    public function removeItem(int $index): void
    {
        if (count($this->items) > 2) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.list.create-list');
    }
}
