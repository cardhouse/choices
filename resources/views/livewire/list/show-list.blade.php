<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $list->title }}
            </h1>
            @if ($list->description)
                <p class="mt-2 text-sm text-gray-600">
                    {{ $list->description }}
                </p>
            @endif
        </div>

        <!-- Items List -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="space-y-4">
                @foreach ($list->items as $item)
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <span class="text-gray-900">{{ $item->label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex justify-center">
            <button wire:click="startVoting" 
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Start Voting
            </button>
        </div>
    </div>
</div> 