<?php

namespace App\Livewire\Templates;

use App\Models\ListTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * List management section: create, view, update, and delete a user's
 * saved list templates, and start new decision lists from them.
 */
#[Layout('layouts.app')]
class ManageTemplates extends Component
{
    /**
     * The template currently being edited, or null when creating a new one.
     */
    public ?int $editingId = null;

    /**
     * Whether the create/edit form is visible.
     */
    public bool $showForm = false;

    /**
     * The template title.
     */
    public string $title = '';

    /**
     * The template description.
     */
    public string $description = '';

    /**
     * The template items.
     *
     * @var array<int, string>
     */
    public array $items = ['', ''];

    /**
     * Open the form to create a new template.
     */
    public function createTemplate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    /**
     * Open the form to edit an existing template.
     */
    public function editTemplate(int $templateId): void
    {
        $template = ListTemplate::findOrFail($templateId);

        Gate::authorize('update', $template);

        $this->editingId = $template->id;
        $this->title = $template->title;
        $this->description = $template->description ?? '';
        $this->items = array_values($template->items);
        $this->showForm = true;
        $this->resetErrorBag();
    }

    /**
     * Save the template being created or edited.
     */
    public function saveTemplate(): void
    {
        $validated = $this->validateTemplate();

        if ($this->editingId !== null) {
            $template = ListTemplate::findOrFail($this->editingId);

            Gate::authorize('update', $template);

            $template->update($validated);

            session()->flash('status', 'Template updated.');
        } else {
            Gate::authorize('create', ListTemplate::class);

            Auth::user()->listTemplates()->create($validated);

            session()->flash('status', 'Template saved.');
        }

        $this->resetForm();
    }

    /**
     * Close the form without saving.
     */
    public function cancelForm(): void
    {
        $this->resetForm();
    }

    /**
     * Delete a template.
     */
    public function deleteTemplate(int $templateId): void
    {
        $template = ListTemplate::findOrFail($templateId);

        Gate::authorize('delete', $template);

        $template->delete();

        if ($this->editingId === $templateId) {
            $this->resetForm();
        }

        session()->flash('status', 'Template deleted.');
    }

    /**
     * Start a new decision list from a template.
     */
    public function useTemplate(int $templateId)
    {
        $template = ListTemplate::findOrFail($templateId);

        Gate::authorize('view', $template);

        return redirect()->route('lists.create', ['template' => $template->id]);
    }

    /**
     * Add a new item to the form.
     */
    public function addItem(): void
    {
        if (count($this->items) < 100) {
            $this->items[] = '';
        }
    }

    /**
     * Remove an item from the form.
     */
    public function removeItem(int $index): void
    {
        if (count($this->items) > 2) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    /**
     * Validate the form and return template attributes ready to persist.
     *
     * @return array{title: string, description: ?string, items: array<int, string>}
     */
    protected function validateTemplate(): array
    {
        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'items' => array_values(array_filter($this->items, fn ($item) => trim($item) !== '')),
        ];

        validator($data, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:2', 'max:100'],
            'items.*' => ['required', 'string', 'min:1', 'max:255'],
        ], [
            'items.required' => 'At least two items are required to save a template.',
            'items.min' => 'At least two items are required to save a template.',
            'items.max' => 'A template cannot contain more than 100 items.',
        ])->validate();

        $data['items'] = array_map(fn ($item) => trim($item), $data['items']);

        return $data;
    }

    /**
     * Reset the form to its initial state.
     */
    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->showForm = false;
        $this->title = '';
        $this->description = '';
        $this->items = ['', ''];
        $this->resetErrorBag();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.templates.manage-templates', [
            'templates' => Auth::user()->listTemplates()->orderByDesc('updated_at')->get(),
        ]);
    }
}
