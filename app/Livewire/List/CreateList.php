<?php

namespace App\Livewire\List;

use App\Http\Requests\StoreListRequest;
use App\Models\DecisionList;
use App\Models\Item;
use App\Services\MatchupGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateList extends Component
{
    /**
     * The current step in the multi-step form.
     *
     * @var int
     */
    public int $currentStep = 1;

    /**
     * The list title.
     *
     * @var string
     */
    public string $title = '';

    /**
     * The list description.
     *
     * @var string|null
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
     *
     * @var bool
     */
    public bool $isAnonymous = false;

    /**
     * The validation rules for step 1.
     *
     * @var array<string, array<int, string>>
     */
    protected array $step1Rules = [
        'title' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string', 'max:1000'],
    ];

    /**
     * The validation rules for step 2.
     *
     * @var array<string, array<int, string>>
     */
    protected array $step2Rules = [
        'items' => ['required', 'array', 'min:2', 'max:100'],
        'items.*' => ['required', 'string', 'min:1', 'max:255'],
        'isAnonymous' => ['boolean'],
    ];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        // Initialize items array with two empty items
        $this->items = ['', ''];

        // If user is not authenticated, force anonymous mode
        if (! Auth::check()) {
            $this->isAnonymous = true;
        }
    }

    /**
     * Add a new item input field.
     */
    public function addItem(): void
    {
        if (count($this->items) >= 100) {
            return;
        }

        $this->items[] = '';
    }

    /**
     * Remove an item input field.
     *
     * @param  int  $index  The index of the item to remove
     */
    public function removeItem(int $index): void
    {
        if (count($this->items) <= 2) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /**
     * Go to the next step in the form.
     */
    public function nextStep(): void
    {
        $this->validate($this->step1Rules);
        $this->currentStep++;
    }

    /**
     * Go to the previous step in the form.
     */
    public function previousStep(): void
    {
        $this->currentStep--;
    }

    /**
     * Create the list and its items.
     */
    public function createList(): void
    {
        try {
            // If we're on step 1 and we have items, we're being called directly
            if ($this->currentStep === 1 && !empty($this->items)) {
                // Validate all fields
                $this->validate(array_merge($this->step1Rules, $this->step2Rules));
            } else if ($this->currentStep === 1) {
                // Normal multi-step flow
                $this->validate($this->step1Rules);
                $this->currentStep++;
                return;
            } else {
                // Step 2 validation
                $this->validate(array_merge($this->step1Rules, $this->step2Rules));
            }

            // Filter out empty items
            $this->items = array_values(array_filter($this->items, fn($item) => trim($item) !== ''));

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
                $items = collect($this->items)
                    ->map(function ($label) {
                        return new Item([
                            'label' => trim($label),
                        ]);
                    });

                $list->items()->saveMany($items);

                // Generate matchups
                app(MatchupGenerator::class)->forList($list);

                // If anonymous, schedule deletion
                if ($this->isAnonymous) {
                    $list->scheduleDeletion();
                }

                return $list;
            });

            if (!$list) {
                throw new \Exception('Failed to create list');
            }

            // Redirect to the list view
            $this->redirect(route('lists.show', ['list' => $list->id]), navigate: true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed when creating list', [
                'errors' => $e->errors(),
                'data' => [
                    'title' => $this->title,
                    'description' => $this->description,
                    'items' => $this->items,
                    'isAnonymous' => $this->isAnonymous,
                ],
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to create list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'title' => $this->title,
                    'description' => $this->description,
                    'items' => $this->items,
                    'isAnonymous' => $this->isAnonymous,
                ],
            ]);
            throw $e;
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