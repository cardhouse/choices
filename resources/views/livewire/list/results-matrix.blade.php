{{--
    =====================================
    Results Matrix Table (Mobile Responsive)
    =====================================
    - Responsive paddings and text sizes
    - Table scrollable and readable on mobile
    =====================================
--}}
<div class="bg-white rounded-2xl shadow-lg p-4 sm:p-8 mb-8">
    <div class="mb-6 sm:mb-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-2">Results Matrix</h2>
        <p class="text-base sm:text-lg text-gray-500">See the outcome of every head-to-head matchup.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-xl text-xs sm:text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 sm:px-6 py-3 border-b border-gray-200"></th>
                    @foreach($list->items()->orderBy('id')->get() as $item)
                        <th class="px-2 sm:px-6 py-3 border-b border-gray-200 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $item->label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($list->items()->orderBy('id')->get() as $rowItem)
                    <tr>
                        <td class="px-2 sm:px-6 py-4 border-b border-gray-200 bg-gray-50 font-semibold text-gray-700">{{ $rowItem->label }}</td>
                        @foreach($list->items()->orderBy('id')->get() as $colItem)
                            <td class="px-2 sm:px-6 py-4 border-b border-gray-200 text-center text-lg">
                                {{ $matrix[$rowItem->id][$colItem->id] ?? '' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div> 