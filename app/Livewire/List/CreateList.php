<?php

namespace App\Livewire\List;

use App\Actions\Lists\CreateList as CreateListAction;
use App\Http\Requests\StoreListRequest;
use App\Models\ListTemplate;
use App\Support\ExampleLists;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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
     * Whether to also save this list as a reusable template.
     */
    public bool $saveAsTemplate = false;

    /**
     * Mount the component and initialize with an example or saved template if provided.
     */
    public function mount(): void
    {
        if ($example = session('example_list')) {
            $this->title = $example['title'];
            $this->description = $example['description'];
            $this->items = $example['items'];
            session()->forget('example_list');

            return;
        }

        if ($templateId = request()->query('template')) {
            $template = ListTemplate::find($templateId);

            if ($template && Auth::check() && Gate::allows('view', $template)) {
                $this->fillFromTemplate($template);
            }
        }
    }

    /**
     * Prefill the form from one of the user's saved templates.
     */
    public function applyTemplate(int $templateId): void
    {
        $template = ListTemplate::findOrFail($templateId);

        Gate::authorize('view', $template);

        $this->fillFromTemplate($template);
        $this->resetErrorBag();
    }

    /**
     * Prefill the form from a pre-built example list.
     */
    public function applyExample(int $index): void
    {
        $examples = ExampleLists::all();

        if (! isset($examples[$index])) {
            return;
        }

        $this->title = $examples[$index]['title'];
        $this->description = $examples[$index]['description'];
        $this->items = $examples[$index]['items'];
        $this->resetErrorBag();
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

        if ($this->saveAsTemplate && Auth::check()) {
            Auth::user()->listTemplates()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'items' => array_map(fn ($item) => trim($item), $validated['items']),
            ]);
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
     * Fill the form fields from a saved template.
     */
    protected function fillFromTemplate(ListTemplate $template): void
    {
        $this->title = $template->title;
        $this->description = $template->description ?? '';
        $this->items = array_values($template->items);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.list.create-list', [
            'savedTemplates' => Auth::check()
                ? Auth::user()->listTemplates()->orderByDesc('updated_at')->get()
                : collect(),
            'exampleLists' => ExampleLists::all(),
        ]);
    }
}
