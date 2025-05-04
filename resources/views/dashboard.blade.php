{{--
    =====================================
    Dashboard View (Mobile Responsive + Collapsible Actions)
    =====================================
    - Responsive paddings and text sizes
    - Table and stats readable on mobile
    - Action buttons stack vertically on mobile
    - Less important columns hidden on mobile
    - Collapsible actions menu for mobile
    =====================================
--}}
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-white py-8 px-2 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl sm:text-4xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-base sm:text-lg text-gray-500 mt-1">Welcome back, {{ Auth::user()->name }}! Here's your decision-making activity.</p>
        </div>

        <!-- Stats (Stacked on mobile) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8">
            <div class="bg-white p-4 sm:p-6 rounded-3xl shadow hover:shadow-xl transition">
                <div class="flex items-center space-x-4">
                    <div class="p-2 sm:p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl sm:text-2xl font-bold text-blue-600">{{ $totalDecisions }}</p>
                        <p class="text-xs sm:text-sm text-gray-600">Total Decisions Made</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-3xl shadow hover:shadow-xl transition">
                <div class="flex items-center space-x-4">
                    <div class="p-2 sm:p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M3 7v4a1 1 0 001 1h3v2a1 1 0 001 1h2v2a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 00-1-1h-3V9a1 1 0 00-1-1H7V6a1 1 0 00-1-1H3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl sm:text-2xl font-bold text-green-600">{{ $listsCount }}</p>
                        <p class="text-xs sm:text-sm text-gray-600">Lists Created</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-3xl shadow hover:shadow-xl transition">
                <div class="flex items-center space-x-4">
                    <div class="p-2 sm:p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl sm:text-2xl font-bold text-yellow-600">{{ $votesCount }}</p>
                        <p class="text-xs sm:text-sm text-gray-600">Votes Cast</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- List Table -->
        <div class="bg-white rounded-3xl shadow p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-2">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Your Lists</h2>
                <a href="{{ route('lists.create') }}" class="text-blue-600 font-medium hover:underline">+ New List</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs sm:text-sm text-left">
                    <thead class="text-gray-500 uppercase tracking-wider bg-gray-50">
                        <tr>
                            <th class="px-2 sm:px-6 py-3">Title</th>
                            <th class="px-2 sm:px-6 py-3 hidden sm:table-cell">Created</th>
                            <th class="px-2 sm:px-6 py-3">Status</th>
                            <th class="px-2 sm:px-6 py-3 text-center hidden sm:table-cell">Items</th>
                            <th class="px-2 sm:px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($lists as $list)
                            <tr class="hover:bg-gray-50">
                                <td class="px-2 sm:px-6 py-4 text-gray-800">{{ $list->title }}</td>
                                <td class="px-2 sm:px-6 py-4 text-gray-500 hidden sm:table-cell">{{ $list->created_at->format('M d, Y') }}</td>
                                <td class="px-2 sm:px-6 py-4">
                                    @if($list->status === 'completed')
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">Completed</span>
                                    @elseif($list->status === 'anonymous')
                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs font-semibold">Anonymous</span>
                                    @elseif($list->status === 'pending')
                                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-semibold">Active</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-xs font-semibold">Other</span>
                                    @endif
                                </td>
                                <td class="px-2 sm:px-6 py-4 text-center hidden sm:table-cell">{{ $list->items_count }}</td>
                                <!-- Actions: Collapsible on mobile, full on desktop -->
                                <td class="px-2 sm:px-6 py-4 text-center">
                                    <!-- Desktop: Show all action buttons -->
                                    <div class="hidden sm:flex flex-row gap-2 justify-center">
                                        <a href="{{ route('lists.show', ['list' => $list]) }}" class="px-3 py-1 text-xs sm:text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200">View</a>
                                        @if($list->status === 'pending')
                                            <a href="{{ route('lists.vote', ['list' => $list]) }}" class="px-3 py-1 text-xs sm:text-sm bg-green-100 text-green-700 rounded hover:bg-green-200">Vote</a>
                                        @endif
                                        @if($list->status === 'completed')
                                            <a href="{{ route('lists.results', ['list' => $list]) }}" class="px-3 py-1 text-xs sm:text-sm bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200">Results</a>
                                        @endif
                                    </div>
                                    <!-- Mobile: Collapsible actions menu -->
                                    <div class="sm:hidden flex justify-center">
                                        <div x-data="{ open: false }" class="relative">
                                            <button @click="open = !open" class="px-2 py-1 rounded bg-gray-100 text-gray-700 hover:bg-gray-200 focus:outline-none">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v.01M12 12v.01M12 18v.01" />
                                                </svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-32 bg-white border rounded shadow-lg z-20">
                                                <a href="{{ route('lists.show', ['list' => $list]) }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">View</a>
                                                @if($list->status === 'pending')
                                                    <a href="{{ route('lists.vote', ['list' => $list]) }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">Vote</a>
                                                @endif
                                                @if($list->status === 'completed')
                                                    <a href="{{ route('lists.results', ['list' => $list]) }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">Results</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 sm:px-6 py-8 text-center text-gray-400">You haven't created any lists yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
