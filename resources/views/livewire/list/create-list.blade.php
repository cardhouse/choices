<div class="max-w-2xl mx-auto p-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('Create a Decision List') }}
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Create a list of items to help make decisions through head-to-head voting.') }}
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex items-center relative">
                        <div class="rounded-full transition duration-500 ease-in-out h-12 w-12 py-3 border-2 {{ $currentStep >= 1 ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bookmark">
                                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"></path>
                            </svg>
                        </div>
                        <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium {{ $currentStep >= 1 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ __('List Details') }}
                        </div>
                    </div>
                    <div class="flex-auto border-t-2 transition duration-500 ease-in-out {{ $currentStep >= 2 ? 'border-blue-600' : 'border-gray-300 dark:border-gray-600' }}"></div>
                    <div class="flex items-center relative">
                        <div class="rounded-full transition duration-500 ease-in-out h-12 w-12 py-3 border-2 {{ $currentStep >= 2 ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list">
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                            </svg>
                        </div>
                        <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium {{ $currentStep >= 2 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ __('Add Items') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1: List Details -->
        @if ($currentStep === 1)
            <div class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Title') }}
                    </label>
                    <div class="mt-1">
                        <input
                            type="text"
                            id="title"
                            wire:model="title"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            required
                        >
                        @error('title')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Description') }} ({{ __('Optional') }})
                    </label>
                    <div class="mt-1">
                        <textarea
                            id="description"
                            wire:model="description"
                            rows="3"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        ></textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if (Auth::check())
                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            id="isAnonymous"
                            wire:model="isAnonymous"
                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                        >
                        <label for="isAnonymous" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            {{ __('Create as anonymous list') }}
                        </label>
                    </div>
                @endif
            </div>
        @endif

        <!-- Step 2: Add Items -->
        @if ($currentStep === 2)
            <div class="space-y-6">
                <div class="space-y-4">
                    @foreach ($items as $index => $item)
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <input
                                    type="text"
                                    wire:model="items.{{ $index }}"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    placeholder="{{ __('Item') }} {{ $index + 1 }}"
                                    required
                                >
                                @error("items.{$index}")
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            @if (count($items) > 2)
                                <button
                                    type="button"
                                    wire:click="removeItem({{ $index }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if (count($items) < 100)
                    <button
                        type="button"
                        wire:click="addItem"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Add Item') }}
                    </button>
                @endif
            </div>
        @endif

        <!-- Navigation Buttons -->
        <div class="mt-8 flex justify-between">
            @if ($currentStep > 1)
                <button
                    type="button"
                    wire:click="previousStep"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    {{ __('Previous') }}
                </button>
            @else
                <div></div>
            @endif

            @if ($currentStep < 2)
                <button
                    type="button"
                    wire:click="nextStep"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    {{ __('Next') }}
                </button>
            @else
                <button
                    type="button"
                    wire:click="createList"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    {{ __('Create List') }}
                </button>
            @endif
        </div>
    </div>
</div> 