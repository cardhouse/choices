{{--
    =====================================
    Ranked Results (Mobile Responsive)
    =====================================
    - Winner callout, victory totals, tiebreaker labels
    - Owner-only analytics: voter count and head-to-head matrix
    =====================================
--}}
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-white py-8 px-2 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8 sm:mb-12">
            <h1 class="text-2xl sm:text-4xl font-bold text-gray-900">{{ $list->title }} &mdash; Results</h1>
            <p class="text-base sm:text-lg text-gray-500 mt-1">
                Ranked by total victories across all voters.
            </p>
            @if ($votingStillOpen)
                <p class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-50 text-yellow-800">
                    Voting is still open &mdash; these standings can change.
                </p>
            @endif
            @if ($voterCount !== null)
                <p class="mt-2 text-sm sm:text-base text-gray-500">
                    {{ $voterCount }} {{ Str::plural('voter', $voterCount) }} participated.
                </p>
            @endif
        </div>

        @if ($results->isNotEmpty() && ! $votingStillOpen)
            <!-- Winner Callout -->
            <div class="mb-8 rounded-2xl bg-yellow-50 border border-yellow-200 p-4 sm:p-6 text-center">
                <p class="text-sm uppercase tracking-wider text-yellow-700 font-semibold mb-1">Winner</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">🏆 {{ $results->first()['item']->label }}</p>
                <p class="text-gray-600 mt-1">{{ $results->first()['score'] }} {{ Str::plural('victory', $results->first()['score']) }}</p>
                @if ($results->first()['tiebreaker'] === 'random')
                    <p class="mt-2 text-sm text-yellow-700">Tie broken by random draw.</p>
                @elseif ($results->first()['tiebreaker'] === 'head_to_head')
                    <p class="mt-2 text-sm text-yellow-700">Tie broken by head-to-head result.</p>
                @endif
            </div>
        @endif

        <!-- Results Table -->
        <div class="bg-white rounded-3xl shadow p-4 sm:p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs sm:text-sm text-left">
                    <thead class="text-gray-500 uppercase tracking-wider bg-gray-50">
                        <tr>
                            <th class="px-2 sm:px-6 py-3">Rank</th>
                            <th class="px-2 sm:px-6 py-3">Item</th>
                            <th class="px-2 sm:px-6 py-3 text-center">Victories</th>
                            <th class="px-2 sm:px-6 py-3">Tiebreaker</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($results as $result)
                            <tr class="hover:bg-gray-50">
                                <td class="px-2 sm:px-6 py-4 text-gray-800">
                                    @if($result['rank'] === 1)
                                        <span class="text-yellow-500">🥇</span>
                                    @elseif($result['rank'] === 2)
                                        <span class="text-gray-400">🥈</span>
                                    @elseif($result['rank'] === 3)
                                        <span class="text-amber-600">🥉</span>
                                    @else
                                        #{{ $result['rank'] }}
                                    @endif
                                </td>
                                <td class="px-2 sm:px-6 py-4 text-gray-800">{{ $result['item']->label }}</td>
                                <td class="px-2 sm:px-6 py-4 text-center text-gray-800">{{ $result['score'] }}</td>
                                <td class="px-2 sm:px-6 py-4 text-gray-500">
                                    @if($result['tiebreaker'] === 'head_to_head')
                                        Head-to-head
                                    @elseif($result['tiebreaker'] === 'random')
                                        Random draw
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($showDetails)
            <!-- Owner Analytics: full head-to-head matrix -->
            <div class="mt-8">
                <h2 class="text-lg sm:text-2xl font-bold text-gray-900 mb-4">Head-to-Head Breakdown</h2>
                <livewire:list.results-matrix :list="$list" :show-vote-counts="true" />
            </div>
        @endif
    </div>
</div>
