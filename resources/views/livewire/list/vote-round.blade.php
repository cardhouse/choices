<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Progress</span>
                <span class="text-sm font-medium text-gray-700">{{ $progress }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
            </div>
        </div>

        <!-- Current Matchup -->
        @if($currentMatchup)
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-center mb-6">
                    <h2 class="text-lg font-medium text-gray-900">Which do you prefer?</h2>
                    <p class="mt-1 text-sm text-gray-500">Matchup {{ $completedMatchups + 1 }} of {{ $totalMatchups }}</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- Item A -->
                    <button wire:click="vote({{ $currentMatchup->item_a_id }})"
                            class="relative block w-full p-6 border-2 border-gray-300 rounded-lg hover:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <span class="text-lg font-medium text-gray-900">
                            {{ $currentMatchup->itemA->label }}
                        </span>
                    </button>

                    <!-- Item B -->
                    <button wire:click="vote({{ $currentMatchup->item_b_id }})"
                            class="relative block w-full p-6 border-2 border-gray-300 rounded-lg hover:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <span class="text-lg font-medium text-gray-900">
                            {{ $currentMatchup->itemB->label }}
                        </span>
                    </button>
                </div>
            </div>
        @else
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900">Voting Complete!</h2>
                <p class="mt-2 text-gray-600">All matchups have been completed.</p>
                <div class="mt-6">
                    <a href="{{ route('lists.show', ['list' => $list]) }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        View Results
                    </a>
                </div>
            </div>
        @endif

        @error('vote')
            <div class="mt-4 text-center text-sm text-red-600">
                {{ $message }}
            </div>
        @enderror
    </div>
</div> 