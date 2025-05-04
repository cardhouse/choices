<div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Ranked Results</h2>
        <p class="text-lg text-gray-500">See how your items performed in head-to-head voting.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Rank</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Wins</th>
                    @if($showVoteCounts)
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Votes</th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($results as $result)
                    <tr>
                        <td class="px-6 py-4 border-b border-gray-200 text-xl text-center">
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
                        <td class="px-6 py-4 border-b border-gray-200 text-lg">
                            {{ $result['item']->label }}
                        </td>
                        <td class="px-6 py-4 border-b border-gray-200 text-center text-lg">
                            {{ $result['score'] }}
                        </td>
                        @if($showVoteCounts)
                            <td class="px-6 py-4 border-b border-gray-200 text-center text-lg">
                                {{ $result['item']->votes_count ?? 0 }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div> 
</div> 