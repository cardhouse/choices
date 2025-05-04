<div class="min-h-screen bg-gradient-to-br from-gray-50 to-white py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-12">
            <h1 class="text-4xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-lg text-gray-500 mt-1">Welcome back, {{ Auth::user()->name }}! Here's your decision-making activity.</p>
        </div>

        <!-- Stats -->
        <div class="hidden sm:grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <div class="bg-white p-6 rounded-3xl shadow hover:shadow-xl transition">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-blue-600">{{ $totalDecisions }}</p>
                        <p class="text-sm text-gray-600">Total Decisions Made</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow hover:shadow-xl transition">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M3 7v4a1 1 0 001 1h3v2a1 1 0 001 1h2v2a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 00-1-1h-3V9a1 1 0 00-1-1H7V6a1 1 0 00-1-1H3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600">{{ $listsCount }}</p>
                        <p class="text-sm text-gray-600">Lists Created</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow hover:shadow-xl transition">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-yellow-600">{{ $votesCount }}</p>
                        <p class="text-sm text-gray-600">Votes Cast</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- List Table -->
        <div class="bg-white rounded-3xl shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Your Lists</h2>
                <a href="{{ route('lists.create') }}" class="text-blue-600 font-medium hover:underline">+ New List</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="text-gray-500 uppercase tracking-wider bg-gray-50">
                        <tr>
                            <th class="px-6 py-3">Title</th>
                            <th class="px-6 py-3">Created</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-center">Items</th>
                            <th class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($lists as $list)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-800">{{ $list->title }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $list->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
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
                                <td class="px-6 py-4 text-center">{{ $list->items_count }}</td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <a href="{{ route('lists.show', ['list' => $list]) }}" class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200">View</a>
                                    @if($list->status === 'pending')
                                        <a href="{{ route('lists.vote', ['list' => $list]) }}" class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded hover:bg-green-200">Vote</a>
                                    @endif
                                    @if($list->status === 'completed')
                                        <a href="{{ route('lists.results', ['list' => $list]) }}" class="px-3 py-1 text-sm bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200">Results</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">You haven't created any lists yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
