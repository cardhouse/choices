{{--
    =====================================
    Create List View (Mobile Responsive)
    =====================================
    - Responsive paddings and text sizes
    - Form actions stack vertically on mobile
    =====================================
--}}
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-white py-8 px-2 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8 sm:mb-10 text-center">
            <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 mb-2">Create a Decision List</h1>
            <p class="text-base sm:text-lg text-gray-500">Add items you want to compare and we'll help you make the best choice through head-to-head voting.</p>
        </div>

        <!-- Start from a Template -->
        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-8 mb-8" x-data="{ showTemplates: false }">
            <button type="button" @click="showTemplates = !showTemplates" class="w-full flex items-center justify-between text-left">
                <div>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">Start from a Template</h2>
                    <p class="text-gray-500 text-sm sm:text-base">Use one of your saved templates or a pre-built example to fill in the form.</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 shrink-0 transition-transform" :class="{ 'rotate-180': showTemplates }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="showTemplates" x-collapse class="mt-6 space-y-6">
                @auth
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Your Saved Templates</h3>
                        @if ($savedTemplates->isEmpty())
                            <p class="text-gray-500 text-sm">
                                You haven't saved any templates yet.
                                <a href="{{ route('templates.index') }}" class="text-blue-600 hover:underline">Manage your templates</a>
                                or check "Save as template" below when creating this list.
                            </p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach ($savedTemplates as $template)
                                    <button type="button" wire:click="applyTemplate({{ $template->id }})" wire:key="saved-template-{{ $template->id }}"
                                        class="px-4 py-2 rounded-xl border border-blue-200 bg-blue-50 text-blue-700 font-semibold text-sm hover:bg-blue-100 transition">
                                        {{ $template->title }}
                                        <span class="text-blue-400 font-normal">({{ count($template->items) }} items)</span>
                                    </button>
                                @endforeach
                            </div>
                            <p class="mt-2 text-xs text-gray-400">
                                <a href="{{ route('templates.index') }}" class="text-blue-600 hover:underline">Manage your templates</a>
                            </p>
                        @endif
                    </div>
                @endauth
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Pre-built Examples</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($exampleLists as $index => $example)
                            <button type="button" wire:click="applyExample({{ $index }})" wire:key="example-{{ $index }}"
                                class="px-4 py-2 rounded-xl border border-gray-200 bg-gray-50 text-gray-700 font-semibold text-sm hover:bg-gray-100 transition">
                                {{ $example['title'] }}
                                <span class="text-gray-400 font-normal">({{ count($example['items']) }} items)</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form wire:submit.prevent="createList" class="space-y-8 sm:space-y-10">
            <!-- List Details Card -->
            <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-8 mb-4">
                <div class="mb-4 sm:mb-6">
                    <label for="title" class="block text-base sm:text-lg font-semibold text-gray-800 mb-2">List Title</label>
                    <input type="text" wire:model="title" id="title"
                        class="w-full rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-3 sm:px-4 py-2 sm:py-3 text-base sm:text-lg transition placeholder-gray-400"
                        placeholder="e.g. Best Vacation Spots">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="description" class="block text-base sm:text-lg font-semibold text-gray-800 mb-2">Description <span class="text-gray-400 text-sm sm:text-base">(Optional)</span></label>
                    <textarea wire:model="description" id="description" rows="3"
                        class="w-full rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-3 sm:px-4 py-2 sm:py-3 text-base sm:text-lg transition resize-none placeholder-gray-400"
                        placeholder="Describe your decision or criteria..."></textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Items to Compare Card -->
            <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-8 mb-4">
                <div class="mb-4 sm:mb-6">
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900 mb-1">Items to Compare</h2>
                    <p class="text-gray-500 mb-4">Add 2–100 items that you want to compare.</p>
                </div>
                <div class="space-y-4" x-data="{
                    async addItemAndFocus() {
                        await this.$wire.addItem();
                        await this.$nextTick();
                        const inputs = document.querySelectorAll('[data-item-input]');
                        inputs[inputs.length - 1]?.focus();
                    }
                }">
                    @foreach($items as $index => $item)
                        <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4">
                            <div class="flex-grow w-full">
                                <input type="text" wire:model="items.{{ $index }}" data-item-input
                                    @keydown.shift.enter.prevent="addItemAndFocus"
                                    class="w-full rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-3 sm:px-4 py-2 sm:py-3 text-base sm:text-lg transition placeholder-gray-400"
                                    placeholder="Enter an item">
                                @error("items.{$index}")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            @if(count($items) > 2)
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="inline-flex items-center p-2 border border-transparent rounded-full shadow text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition">
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
                        class="mt-4 sm:mt-6 w-full flex items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 text-blue-700 font-semibold py-2 sm:py-3 hover:bg-blue-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                        Add Another Item
                    </button>
                    <p class="text-xs text-gray-400 text-center">Tip: press Shift+Enter in an item to add another.</p>
                </div>
            </div>

            @auth
                <!-- Save as Template Card -->
                <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-8 mb-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="saveAsTemplate"
                            class="mt-1 h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>
                            <span class="block text-base sm:text-lg font-semibold text-gray-800">Save as template</span>
                            <span class="block text-gray-500 text-sm sm:text-base">Keep a reusable copy of this list in <a href="{{ route('templates.index') }}" class="text-blue-600 hover:underline">My Templates</a> so you can start from it again later.</span>
                        </span>
                    </label>
                </div>
            @endauth

            @guest
                <!-- Anonymous Notice Card -->
                <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-8 mb-4">
                    <p class="text-gray-500 text-sm sm:text-base">
                        You're not logged in, so this list will be anonymous. Anonymous lists
                        expire after 30 minutes unless you register and claim them.
                    </p>
                </div>
            @endguest

            <!-- Submit Button -->
            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-4">
                <button type="submit"
                    class="inline-flex items-center justify-center w-full sm:w-auto px-6 sm:px-8 py-3 rounded-xl bg-blue-600 text-white text-base sm:text-lg font-bold shadow-lg hover:bg-blue-700 transition">
                    Create List
                </button>
            </div>
        </form>
    </div>
</div> 