{{--
    =====================================
    Show Examples View (Mobile Responsive)
    =====================================
    - Responsive paddings and text sizes
    - Example grid stacks vertically on mobile
    =====================================
--}}
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-white py-8 px-2 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-10 sm:mb-14">
            <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 mb-2">Example Decision Lists</h1>
            <p class="mt-3 max-w-2xl mx-auto text-base sm:text-lg text-gray-500">Get inspired by these examples or create your own list from scratch.</p>
            <div class="mt-6 sm:mt-8">
                <button wire:click="createNew" class="inline-flex items-center justify-center w-full sm:w-auto px-6 sm:px-8 py-3 rounded-xl bg-blue-600 text-white text-base sm:text-lg font-bold shadow-lg hover:bg-blue-700 transition">
                    Create New List
                </button>
            </div>
        </div>

        <!-- Example Lists Grid -->
        <div class="mt-8 sm:mt-12 grid gap-6 sm:gap-10 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($exampleLists as $index => $list)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col h-full">
                    <div class="px-4 sm:px-8 py-6 sm:py-8 flex-1 flex flex-col">
                        <h3 class="text-lg sm:text-2xl font-bold text-gray-900 mb-2">{{ $list['title'] }}</h3>
                        <p class="mb-4 text-gray-500 text-sm sm:text-base">{{ $list['description'] }}</p>
                        <!-- Items Preview -->
                        <div class="mt-2">
                            <h4 class="text-xs sm:text-base font-semibold text-gray-500">Items to Compare:</h4>
                            <ul class="mt-2 divide-y divide-gray-200">
                                @foreach(array_slice($list['items'], 0, 4) as $item)
                                    <li class="py-2 text-xs sm:text-base text-gray-700">{{ $item }}</li>
                                @endforeach
                                @if(count($list['items']) > 4)
                                    <li class="py-2 text-xs sm:text-base text-gray-400 italic">
                                        +{{ count($list['items']) - 4 }} more items...
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="px-4 sm:px-8 py-4 sm:py-6 bg-gray-50">
                        <button wire:click="useExample({{ $index }})" class="w-full inline-flex justify-center items-center px-4 sm:px-6 py-2 sm:py-3 rounded-xl bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100 transition">
                            Use This Template
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div> 