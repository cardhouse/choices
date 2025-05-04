{{--
    =====================================
    Vote Round View (Mobile Responsive)
    =====================================
    - Responsive paddings and text sizes
    - Voting buttons stack vertically on mobile
    =====================================
--}}
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-white py-8 px-2 sm:px-6 lg:px-8" x-data @matchup-updated.window="$wire.$refresh()" @voting-complete.window="$wire.$refresh()">
    <div class="max-w-2xl mx-auto">
        <!-- Progress Bar -->
        <div class="mb-8 sm:mb-10">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm sm:text-base font-medium text-gray-700">Progress</span>
                <span class="text-sm sm:text-base font-medium text-gray-700">{{ $progress }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
            </div>
        </div>

        <!-- Current Matchup -->
        @if($currentMatchup)
            <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-10">
                <div class="text-center mb-6 sm:mb-8">
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">Which do you prefer?</h2>
                    <p class="mt-2 text-base sm:text-lg text-gray-500">Matchup {{ $completedMatchups + 1 }} of {{ $totalMatchups }}</p>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:gap-6 sm:grid-cols-2">
                    <!-- Item A -->
                    <button wire:click="vote({{ $currentMatchup->item_a_id }})"
                            class="relative block w-full p-6 sm:p-8 border-2 border-gray-200 rounded-xl hover:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        <span class="text-lg sm:text-2xl font-semibold text-gray-900">
                            {{ $currentMatchup->itemA->label }}
                        </span>
                    </button>
                    <!-- Item B -->
                    <button wire:click="vote({{ $currentMatchup->item_b_id }})"
                            class="relative block w-full p-6 sm:p-8 border-2 border-gray-200 rounded-xl hover:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        <span class="text-lg sm:text-2xl font-semibold text-gray-900">
                            {{ $currentMatchup->itemB->label }}
                        </span>
                    </button>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-10 text-center">
                <h2 class="text-xl sm:text-3xl font-extrabold text-gray-900">Voting Complete!</h2>
                <p class="mt-4 text-base sm:text-lg text-gray-600">All matchups have been completed.</p>
                <div class="mt-8">
                    <a href="{{ route('lists.show', ['list' => $list]) }}"
                       class="inline-flex items-center justify-center w-full sm:w-auto px-6 sm:px-8 py-3 rounded-xl bg-blue-600 text-white text-base sm:text-lg font-bold shadow-lg hover:bg-blue-700 transition">
                        View Results
                    </a>
                </div>
            </div>
        @endif
        @error('vote')
            <div class="mt-6 text-center text-base text-red-600">
                {{ $message }}
            </div>
        @enderror
    </div>
</div> 