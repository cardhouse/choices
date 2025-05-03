<?php

namespace App\Livewire\List;

use App\Models\DecisionList;
use App\Models\DecisionListItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateList extends Component
{
    /**
     * The list title.
     */
    public string $title = '';

    /**
     * The list description.
     */
    public ?string $description = null;

    /**
     * The list items.
     *
     * @var array<int, string>
     */
    public array $items = ['', ''];

    /**
     * Whether the list is anonymous.
     */
    public bool $isAnonymous = false;

    /**
     * The validation rules for the form.
     *
     * @var array<string, array<int, string>>
     */
    protected array $rules = [
        'title' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string', 'max:1000'],
        'items' => ['required', 'array', 'min:2', 'max:100'],
        'items.*' => ['required', 'string', 'min:1', 'max:255'],
        'isAnonymous' => ['boolean'],
    ];

    /**
     * The validation messages for the form.
     *
     * @var array<string, string>
     */
    protected array $messages = [
        'title.required' => 'The title field is required.',
        'title.max' => 'The title may not be greater than 255 characters.',
        'description.max' => 'The description may not be greater than 1000 characters.',
        'items.required' => 'At least 2 items are required.',
        'items.min' => 'At least 2 items are required.',
        'items.max' => 'You may not have more than 100 items.',
        'items.*.required' => 'Each item is required.',
        'items.*.min' => 'Each item must be at least 1 character.',
        'items.*.max' => 'Each item may not be greater than 255 characters.',
    ];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        // If user is not authenticated, force anonymous mode
        if (! Auth::check()) {
            $this->isAnonymous = true;
        }
    }

    /**
     * Create the list and its items.
     */
    public function createList(): void
    {
        try {
            // Validate all fields
            $validator = Validator::make([
                'title' => $this->title,
                'description' => $this->description,
                'items' => $this->items,
                'isAnonymous' => $this->isAnonymous,
            ], $this->rules, $this->messages);

            // Add custom validation for empty items
            $validator->after(function ($validator) {
                $validItems = array_filter($this->items, fn ($item) => trim($item) !== '');
                if (count($validItems) < 2) {
                    $validator->errors()->add('items', 'At least 2 non-empty items are required.');
                }
            });

            if ($validator->fails()) {
                $this->setErrorBag($validator->errors());

                return;
            }

            // Create the list in a transaction
            $list = DB::transaction(function () {
                // Create the list
                $list = DecisionList::create([
                    'title' => $this->title,
                    'description' => $this->description,
                    'user_id' => $this->isAnonymous ? null : Auth::id(),
                    'is_anonymous' => $this->isAnonymous,
                ]);

                // Create the items
                foreach ($this->items as $item) {
                    if (empty(trim($item))) {
                        continue;
                    }

                    DecisionListItem::create([
                        'list_id' => $list->id,
                        'label' => trim($item),
                    ]);
                }

                return $list;
            });

            // Redirect to the list page
            $this->redirect(route('lists.show', ['list' => $list->id]));
        } catch (\Exception $e) {
            Log::error('Failed to create list: '.$e->getMessage());
            $this->addError('title', 'Failed to create list. Please try again.');
        }
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
