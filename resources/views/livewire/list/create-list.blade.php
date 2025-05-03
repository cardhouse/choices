<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900">
                Create a Decision List
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                Add items you want to compare and we'll help you make the best choice through head-to-head voting.
            </p>
        </div>

        <!-- Form -->
        <form wire:submit.prevent="createList" class="space-y-8">
            <!-- List Details -->
            <div class="bg-white shadow rounded-lg p-6 space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">
                        List Title
                    </label>
                    <div class="mt-1">
                        <input type="text" wire:model="title" id="title"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            placeholder="e.g., Weekend Activities, Project Priorities">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Description (Optional)
                    </label>
                    <div class="mt-1">
                        <textarea wire:model="description" id="description" rows="3"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            placeholder="Add some context about what you're deciding..."></textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Items to Compare</h2>
                    <p class="mt-1 text-sm text-gray-500">Add 2-100 items that you want to compare.</p>
                </div>

                <div class="space-y-4">
                    @foreach($items as $index => $item)
                        <div class="flex items-center gap-4">
                            <div class="flex-grow">
                                <input type="text" wire:model="items.{{ $index }}"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Enter an item">
                                @error("items.{$index}")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            @if(count($items) > 2)
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="inline-flex items-center p-2 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
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

                    <button type="button" wire:click="addItem"
                        class="mt-4 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Another Item
                    </button>
                </div>
            </div>

            <!-- Options -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <input type="checkbox" wire:model="isAnonymous" id="anonymous"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="anonymous" class="ml-2 block text-sm text-gray-900">
                        Create as anonymous list
                    </label>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    Anonymous lists expire after 30 minutes unless you register and claim them.
                </p>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create List
                </button>
            </div>
        </form>
    </div>
</div> 