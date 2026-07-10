<?php

namespace App\Actions\Lists;

use App\Exceptions\InvalidListException;
use App\Models\DecisionList;
use App\Models\Matchup;
use Illuminate\Support\Facades\DB;

/**
 * Precomputes every unique round-robin pairing for a list's items.
 */
class GenerateMatchups
{
    /**
     * @throws InvalidListException
     */
    public function handle(DecisionList $list): void
    {
        $items = $list->items()->get()->all();

        if (count($items) < 2) {
            throw new InvalidListException('List must have at least 2 items to generate matchups');
        }

        DB::transaction(function () use ($list, $items) {
            $count = count($items);

            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    Matchup::create([
                        'list_id' => $list->id,
                        'item_a_id' => $items[$i]->id,
                        'item_b_id' => $items[$j]->id,
                    ]);
                }
            }
        });
    }
}
