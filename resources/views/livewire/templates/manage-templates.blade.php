{{--
    =====================================
    Manage Templates View (Mobile Responsive)
    =====================================
    - Saved templates grid with use/edit/delete actions
    - Inline create/edit form card
    =====================================
--}}
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-white py-8 px-2 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-4xl font-bold text-gray-900">My Templates</h1>
                <p class="text-base sm:text-lg text-gray-500 mt-1">Save lists you use often — like dinner ideas — and re-use them anytime.</p>
            </div>
            <div>
                <button wire:click="createTemplate"
                    class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg hover:bg-blue-700 transition">
                    + New Template
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-xl bg-green-50 border border-green-200 text-green-700 px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        @if ($showForm)
            <!-- Create/Edit Form Card -->
            <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-8 mb-8">
                <h2 class="text-lg sm:text-2xl font-bold text-gray-900 mb-4">
                    {{ $editingId ? 'Edit Template' : 'New Template' }}
                </h2>
                <form wire:submit.prevent="saveTemplate" class="space-y-6">
                    <div>
                        <label for="template-title" class="block text-base font-semibold text-gray-800 mb-2">Title</label>
                        <input type="text" wire:model="title" id="template-title"
                            class="w-full rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-3 sm:px-4 py-2 sm:py-3 transition placeholder-gray-400"
                            placeholder="e.g. Weeknight Dinners">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="template-description" class="block text-base font-semibold text-gray-800 mb-2">Description <span class="text-gray-400 text-sm">(Optional)</span></label>
                        <textarea wire:model="description" id="template-description" rows="2"
                            class="w-full rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-3 sm:px-4 py-2 sm:py-3 transition resize-none placeholder-gray-400"
                            placeholder="What is this template for?"></textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800 mb-2">Items</h3>
                        <div class="space-y-3" x-data="{
                            async addItemAndFocus() {
                                await this.$wire.addItem();
                                await this.$nextTick();
                                const inputs = document.querySelectorAll('[data-item-input]');
                                inputs[inputs.length - 1]?.focus();
                            }
                        }">
                            @foreach ($items as $index => $item)
                                <div class="flex items-center gap-2 sm:gap-4">
                                    <div class="flex-grow">
                                        <input type="text" wire:model="items.{{ $index }}" data-item-input
                                            @keydown.shift.enter.prevent="addItemAndFocus"
                                            class="w-full rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-3 sm:px-4 py-2 transition placeholder-gray-400"
                                            placeholder="Enter an item">
                                        @error("items.{$index}")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    @if (count($items) > 2)
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="inline-flex items-center p-2 rounded-full shadow text-white bg-red-600 hover:bg-red-700 transition">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                            @error('items')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <button type="button" @click="addItemAndFocus"
                                class="w-full flex items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 text-blue-700 font-semibold py-2 hover:bg-blue-100 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                                Add Another Item
                            </button>
                            <p class="text-xs text-gray-400 text-center">Tip: press Shift+Enter in an item to add another.</p>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-4">
                        <button type="button" wire:click="cancelForm"
                            class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 rounded-xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg hover:bg-blue-700 transition">
                            {{ $editingId ? 'Update Template' : 'Save Template' }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Saved Templates Grid -->
        @if ($templates->isEmpty() && ! $showForm)
            <div class="bg-white rounded-2xl shadow p-8 sm:p-12 text-center">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-2">No saved templates yet</h2>
                <p class="text-gray-500 mb-6">Create a template to save a list — like your go-to dinner options — and re-use it whenever you need to decide.</p>
                <button wire:click="createTemplate"
                    class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg hover:bg-blue-700 transition">
                    Create Your First Template
                </button>
            </div>
        @else
            <div class="grid gap-6 sm:gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($templates as $template)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col h-full" wire:key="template-{{ $template->id }}">
                        <div class="px-4 sm:px-6 py-5 sm:py-6 flex-1 flex flex-col">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-1">{{ $template->title }}</h3>
                            @if ($template->description)
                                <p class="mb-3 text-gray-500 text-sm">{{ $template->description }}</p>
                            @endif
                            <div class="mt-1">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ count($template->items) }} items</h4>
                                <ul class="mt-2 divide-y divide-gray-200">
                                    @foreach (array_slice($template->items, 0, 4) as $item)
                                        <li class="py-1.5 text-sm text-gray-700">{{ $item }}</li>
                                    @endforeach
                                    @if (count($template->items) > 4)
                                        <li class="py-1.5 text-sm text-gray-400 italic">
                                            +{{ count($template->items) - 4 }} more items...
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                        <div class="px-4 sm:px-6 py-4 bg-gray-50 flex flex-wrap gap-2">
                            <button wire:click="useTemplate({{ $template->id }})"
                                class="flex-1 inline-flex justify-center items-center px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                                Use Template
                            </button>
                            <button wire:click="editTemplate({{ $template->id }})"
                                class="inline-flex justify-center items-center px-4 py-2 rounded-xl bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100 transition">
                                Edit
                            </button>
                            <button wire:click="deleteTemplate({{ $template->id }})"
                                wire:confirm="Delete this template? This cannot be undone."
                                class="inline-flex justify-center items-center px-4 py-2 rounded-xl bg-red-50 text-red-700 font-semibold hover:bg-red-100 transition">
                                Delete
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
