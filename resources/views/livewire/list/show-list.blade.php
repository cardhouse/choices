{{--
    =====================================
    Show List View (Mobile Responsive)
    =====================================
    - Responsive paddings and text sizes
    - Action buttons stack vertically on mobile
    =====================================
--}}
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-white py-8 px-2 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8 sm:mb-10 text-center">
            <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 mb-2">
                {{ $list->title }}
            </h1>
            @if ($list->description)
                <p class="mt-2 text-base sm:text-lg text-gray-600">
                    {{ $list->description }}
                </p>
            @endif
        </div>

        @if($results)
            <!-- Results View -->
            <livewire:list.ranked-results :list="$list" />
        @else
            <!-- Items List -->
            <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-8 mb-8">
                <div class="space-y-4">
                    @foreach ($list->items as $item)
                        <div class="flex items-center justify-between p-3 sm:p-4 border border-gray-200 rounded-xl">
                            <span class="text-base sm:text-lg text-gray-900">{{ $item->label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
                <button wire:click="startVoting" 
                        class="inline-flex items-center justify-center w-full sm:w-auto px-6 sm:px-8 py-3 rounded-xl bg-blue-600 text-white text-base sm:text-lg font-bold shadow-lg hover:bg-blue-700 transition">
                    Start Voting
                </button>
                @if($list->matchups()->where('status', 'completed')->exists())
                    @if(auth()->check())
                        <a href="{{ route('lists.results', ['list' => $list]) }}"
                           class="inline-flex items-center justify-center w-full sm:w-auto px-6 sm:px-8 py-3 rounded-xl bg-green-600 text-white text-base sm:text-lg font-bold shadow-lg hover:bg-green-700 transition">
                            View Results
                        </a>
                    @else
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center w-full sm:w-auto px-6 sm:px-8 py-3 rounded-xl bg-green-600 text-white text-base sm:text-lg font-bold shadow-lg hover:bg-green-700 transition">
                            Register to View Results
                        </a>
                    @endif
                @endif
            </div>
        @endif
    </div>
</div> 