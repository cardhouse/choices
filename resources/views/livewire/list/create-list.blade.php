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
        <form wire:submit="createList" class="space-y-6">
            <!-- List Details -->
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

            <!-- List Items -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Items') }}
                    </label>
                    @if (count($items) < 100)
                        <button
                            type="button"
                            wire:click="addItem"
                            class="inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <svg class="-ml-1 mr-2 h-4 w-4 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            {{ __('Add Item') }}
                        </button>
                    @endif
                </div>

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
                @error('items')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    {{ __('Create List') }}
                </button>
            </div>
        </form>
    </div>
</div> 