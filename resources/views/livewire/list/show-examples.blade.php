<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                Example Decision Lists
            </h1>
            <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500 sm:mt-4">
                Get inspired by these examples or create your own list from scratch.
            </p>
            <div class="mt-8">
                <button wire:click="createNew" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create New List
                </button>
            </div>
        </div>

        <!-- Example Lists Grid -->
        <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($exampleLists as $index => $list)
                <div class="bg-white overflow-hidden shadow rounded-lg divide-y divide-gray-200">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ $list['title'] }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $list['description'] }}</p>
                        
                        <!-- Items Preview -->
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-500">Items to Compare:</h4>
                            <ul class="mt-2 divide-y divide-gray-200">
                                @foreach(array_slice($list['items'], 0, 4) as $item)
                                    <li class="py-2 text-sm text-gray-600">{{ $item }}</li>
                                @endforeach
                                @if(count($list['items']) > 4)
                                    <li class="py-2 text-sm text-gray-400 italic">
                                        +{{ count($list['items']) - 4 }} more items...
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    
                    <div class="px-4 py-4 sm:px-6">
                        <button wire:click="useExample({{ $index }})" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Use This Template
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div> 