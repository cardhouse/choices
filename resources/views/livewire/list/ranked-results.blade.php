<div>
    <!-- Header -->
    <div class="mb-12">
        <h1 class="text-4xl font-bold text-gray-900">Results</h1>
        <p class="text-lg text-gray-500 mt-1">See how your items performed in head-to-head voting.</p>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-3xl shadow p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead class="text-gray-500 uppercase tracking-wider bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">Rank</th>
                        <th class="px-6 py-3">Item</th>
                        <th class="px-6 py-3 text-center">Wins</th>
                        @if($showVoteCounts)
                            <th class="px-6 py-3 text-center">Votes</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($results as $result)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-800">
                                @if($loop->first)
                                    <span class="text-yellow-500">🥇</span>
                                @elseif($loop->iteration === 2)
                                    <span class="text-gray-400">🥈</span>
                                @elseif($loop->iteration === 3)
                                    <span class="text-amber-600">🥉</span>
                                @else
                                    #{{ $loop->iteration }}
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-800">{{ $result['item']->label }}</td>
                            <td class="px-6 py-4 text-center text-gray-800">{{ $result['score'] }}</td>
                            @if($showVoteCounts)
                                <td class="px-6 py-4 text-center text-gray-800">{{ $result['item']->votes_count ?? 0 }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div> 