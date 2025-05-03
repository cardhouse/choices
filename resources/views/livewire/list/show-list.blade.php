<div class="max-w-2xl mx-auto p-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ $list->title }}
        </h1>
        @if ($list->description)
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ $list->description }}
            </p>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <div class="space-y-4">
            @foreach ($list->items as $item)
                <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <span class="text-gray-900 dark:text-gray-100">{{ $item->label }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div> 