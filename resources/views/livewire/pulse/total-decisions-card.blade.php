<x-pulse::card :cols="$cols" :rows="$rows" :class="$class" wire:poll.5s="">
    <x-pulse::card-header name="Total Decisions">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </x-slot:icon>
    </x-pulse::card-header>

    <div class="p-4">
        <div class="text-4xl font-bold text-gray-900">{{ number_format($totalDecisions) }}</div>
        <div class="mt-1 text-sm text-gray-500">Total completed decisions across all lists</div>
    </div>
</x-pulse::card> 