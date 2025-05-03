<div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-200">
        <thead>
            <tr>
                <th class="px-4 py-2 border-b border-gray-200 bg-gray-50"></th>
                @foreach($list->items()->orderBy('id')->get() as $item)
                    <th class="px-4 py-2 border-b border-gray-200 bg-gray-50 text-center">{{ $item->label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($list->items()->orderBy('id')->get() as $rowItem)
                <tr>
                    <td class="px-4 py-2 border-b border-gray-200 bg-gray-50">{{ $rowItem->label }}</td>
                    @foreach($list->items()->orderBy('id')->get() as $colItem)
                        <td class="px-4 py-2 border-b border-gray-200 text-center">
                            {{ $matrix[$rowItem->id][$colItem->id] ?? '' }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div> 