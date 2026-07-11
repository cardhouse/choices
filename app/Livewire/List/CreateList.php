<?php

namespace App\Livewire\List;

use App\Actions\Lists\CreateList as CreateListAction;
use App\Http\Requests\StoreListRequest;
use App\Models\DecisionList;
use App\Models\ListTemplate;
use App\Support\ExampleLists;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
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
     * Whether the form was pre-filled from a saved template via the URL.
     * When true, the user is first offered "Use as is" or "Make Updates".
     */
    public bool $fromTemplate = false;

    /**
     * Whether the editable form is revealed. For a template-backed list this
     * only becomes true once the user chooses "Make Updates".
     */
    public bool $editing = false;

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
            $this->editing = true;

            return;
        }

        if ($templateId = request()->query('template')) {
            $template = ListTemplate::find($templateId);

            if ($template && Auth::check() && Gate::allows('view', $template)) {
                $this->fillFromTemplate($template);
                $this->fromTemplate = true;

                return;
            }
        }

        // A blank list starts straight in the editable form.
        $this->editing = true;
    }

    /**
     * Reveal the editable form so the user can add or remove items before
     * creating the list from a template.
     */
    public function makeUpdates(): void
    {
        $this->editing = true;
    }

    /**
     * Return to the "Use as is" / "Make Updates" choice for a template.
     */
    public function backToOptions(): void
    {
        if ($this->fromTemplate) {
            $this->editing = false;
            $this->resetErrorBag();
        }
    }

    /**
     * Create the list from the template unchanged and jump straight into voting.
     */
    public function useAsIs()
    {
        try {
            $validated = $this->validatedData();
        } catch (ValidationException $e) {
            // The template's own data won't validate (edge case); reveal the
            // form so the user can fix it rather than failing silently.
            $this->editing = true;

            throw $e;
        }

        $list = $this->persistList($validated);

        if ($list === null) {
            return;
        }

        return $this->redirect(route('lists.vote', ['list' => $list->id]));
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
    public function createList()
    {
        $validated = $this->validatedData();

        $list = $this->persistList($validated);

        if ($list === null) {
            return;
        }

        if ($this->saveAsTemplate && Auth::check()) {
            Auth::user()->listTemplates()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'items' => array_map(fn ($item) => trim($item), $validated['items']),
            ]);
        }

        return $this->redirect(route('lists.show', ['list' => $list->id]));
    }

    /**
     * Validate the current form state and return the sanitized attributes.
     *
     * @return array{title: string, description: ?string, items: array<int, string>}
     */
    protected function validatedData(): array
    {
        $request = new StoreListRequest;

        return Validator::make(
            [
                'title' => $this->title,
                'description' => $this->description ?: null,
                'items' => array_values(array_filter($this->items, fn ($item) => trim($item) !== '')),
            ],
            $request->rules(),
            $request->messages(),
        )->validate();
    }

    /**
     * Persist the list, returning null (and surfacing an error) on failure.
     *
     * @param  array{title: string, description?: ?string, items: array<int, string>}  $validated
     */
    protected function persistList(array $validated): ?DecisionList
    {
        try {
            return app(CreateListAction::class)->handle($validated, Auth::user());
        } catch (\Exception $e) {
            Log::error('Failed to create list: '.$e->getMessage());
            $this->addError('title', 'Failed to create list. Please try again.');

            return null;
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
