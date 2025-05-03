<?php

namespace App\Services;

use App\Exceptions\InvalidListException;
use App\Models\DecisionList;
use App\Models\Item;
use App\Models\Matchup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MatchupGenerator
{
    /**
     * Generate all unique matchups for a given list
     *
     * @param DecisionList $list
     * @return void
     * @throws InvalidListException
     */
    public static function forList(DecisionList $list): void
    {
        try {
            // Validate list has at least 2 items
            if ($list->items->count() < 2) {
                throw new InvalidListException("List must have at least 2 items to generate matchups");
            }

            DB::transaction(function () use ($list) {
                $items = $list->items->all();
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

            Log::info("Successfully generated matchups for list {$list->id}");
        } catch (\Exception $e) {
            Log::error("Failed to generate matchups for list {$list->id}: {$e->getMessage()}");
            throw $e;
        }
    }
} 