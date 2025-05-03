<?php

namespace App\Services;

use App\Models\DecisionList;
use App\Models\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Service class responsible for calculating and ranking scores for items in a decision list.
 *
 * This service handles:
 * - Tallying wins for each item based on completed matchups
 * - Applying deterministic tiebreaker logic
 * - Returning ordered results with scores
 */
class ScoreCalculator
{
    /**
     * Calculate and return ranked scores for all items in a decision list.
     *
     * @param  DecisionList  $list  The decision list to calculate scores for
     * @return Collection Collection of items with their scores and rankings
     *
     * @throws \RuntimeException If there are inconsistencies in the matchup data
     */
    public function forList(DecisionList $list): Collection
    {
        try {
            // Verify list exists in database
            if (! $list->exists) {
                throw new \RuntimeException('List does not exist in database');
            }

            // Get all completed matchups for the list
            $matchups = $list->matchups()
                ->whereNotNull('winner_item_id')
                ->get();

            // Count wins for each item
            $wins = $matchups->countBy('winner_item_id');

            // Get all items with their scores
            $items = $list->items()
                ->get()
                ->map(function (Item $item) use ($wins) {
                    return [
                        'item' => $item,
                        'score' => $wins->get($item->id, 0),
                    ];
                });

            // Sort by score (descending) and then by label (ascending) for tiebreakers
            $sorted = $items->sortByDesc('score')
                ->groupBy('score')
                ->map(function ($group) {
                    return $group->sortBy(function ($item) {
                        return $item['item']->label;
                    });
                })
                ->flatten(1);

            // Add rankings
            $rank = 1;
            $previousScore = null;
            $itemsWithRankings = collect();

            foreach ($sorted as $item) {
                // Keep same rank for tied scores
                if ($previousScore !== null && $item['score'] < $previousScore) {
                    $rank = $itemsWithRankings->count() + 1;
                }

                $itemsWithRankings->push([
                    'item' => $item['item'],
                    'score' => $item['score'],
                    'rank' => $rank,
                ]);

                $previousScore = $item['score'];
            }

            return $itemsWithRankings;
        } catch (\Exception $e) {
            Log::error('Error calculating scores for list', [
                'list_id' => $list->id,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Failed to calculate scores: '.$e->getMessage());
        }
    }
}
