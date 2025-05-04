<div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Results Matrix</h2>
        <p class="text-lg text-gray-500">See the outcome of every head-to-head matchup.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-xl">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 border-b border-gray-200"></th>
                    @foreach($list->items()->orderBy('id')->get() as $item)
                        <th class="px-6 py-3 border-b border-gray-200 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $item->label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($list->items()->orderBy('id')->get() as $rowItem)
                    <tr>
                        <td class="px-6 py-4 border-b border-gray-200 bg-gray-50 font-semibold text-gray-700">{{ $rowItem->label }}</td>
                        @foreach($list->items()->orderBy('id')->get() as $colItem)
                            <td class="px-6 py-4 border-b border-gray-200 text-center text-lg">
                                {{ $matrix[$rowItem->id][$colItem->id] ?? '' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div> 