<div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Wins</th>
                    @if($showVoteCounts)
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Votes</th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($results as $result)
                    <tr>
                        <td class="px-4 py-2 border-b border-gray-200">
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
                        <td class="px-4 py-2 border-b border-gray-200">
                            {{ $result['item']->label }}
                        </td>
                        <td class="px-4 py-2 border-b border-gray-200 text-center">
                            {{ $result['score'] }}
                        </td>
                        @if($showVoteCounts)
                            <td class="px-4 py-2 border-b border-gray-200 text-center">
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