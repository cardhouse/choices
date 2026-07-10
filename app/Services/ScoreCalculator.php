<?php

namespace App\Services;

use App\Models\DecisionList;
use App\Models\DecisionListItem;
use Illuminate\Support\Collection;

/**
 * Query class that ranks a list's items by total victories.
 *
 * An item's score is the total number of votes it received across all
 * matchups and all voters. Ties are broken per the spec:
 *  - two-way tie: the head-to-head matchup between the tied items decides
 *  - still tied, or a tie between three or more items: a random (but
 *    deterministic per list) order, flagged as such in the output
 */
class ScoreCalculator
{
    /**
     * @return Collection<int, array{item: DecisionListItem, score: int, rank: int, tiebreaker: ?string}>
     */
    public function forList(DecisionList $list): Collection
    {
        $matchups = $list->matchups()->get()->keyBy('id');

        $votes = $list->votes()->get();

        $scores = $votes->countBy('chosen_item_id');

        $items = $list->items()
            ->get()
            ->map(fn (DecisionListItem $item) => [
                'item' => $item,
                'score' => (int) $scores->get($item->id, 0),
                'tiebreaker' => null,
            ]);

        $ranked = $items
            ->groupBy('score')
            ->sortKeysDesc()
            ->flatMap(fn (Collection $tied) => $this->breakTie($tied, $matchups, $votes, $list))
            ->values();

        return $ranked->map(fn (array $entry, int $index) => [
            ...$entry,
            'rank' => $index + 1,
        ]);
    }

    /**
     * How many distinct voters have cast at least one vote on the list.
     */
    public function voterCountForList(DecisionList $list): int
    {
        return $list->votes()
            ->get()
            ->unique(fn ($vote) => $vote->user_id !== null ? "u:{$vote->user_id}" : "s:{$vote->session_token}")
            ->count();
    }

    /**
     * Order a group of equally-scored items.
     *
     * @param  Collection<int, array{item: DecisionListItem, score: int, tiebreaker: ?string}>  $tied
     */
    private function breakTie(Collection $tied, Collection $matchups, Collection $votes, DecisionList $list): Collection
    {
        if ($tied->count() === 1) {
            return $tied;
        }

        if ($tied->count() === 2) {
            $resolved = $this->breakTwoWayTie($tied->values(), $matchups, $votes);

            if ($resolved !== null) {
                return $resolved;
            }
        }

        // Random order, deterministic per list so results don't reshuffle on
        // every page load.
        return $tied
            ->sortBy(fn (array $entry) => md5($list->id.'-'.$entry['item']->id))
            ->map(fn (array $entry) => [...$entry, 'tiebreaker' => 'random'])
            ->values();
    }

    /**
     * Try to resolve a two-way tie using the tied items' head-to-head matchup.
     * Returns null when the head-to-head is also tied (or was never voted on).
     *
     * @param  Collection<int, array{item: DecisionListItem, score: int, tiebreaker: ?string}>  $tied
     */
    private function breakTwoWayTie(Collection $tied, Collection $matchups, Collection $votes): ?Collection
    {
        [$first, $second] = [$tied[0], $tied[1]];

        $headToHead = $matchups->first(
            fn ($matchup) => $matchup->involves($first['item']->id) && $matchup->involves($second['item']->id)
        );

        if ($headToHead === null) {
            return null;
        }

        $matchupVotes = $votes->where('matchup_id', $headToHead->id);
        $firstVotes = $matchupVotes->where('chosen_item_id', $first['item']->id)->count();
        $secondVotes = $matchupVotes->where('chosen_item_id', $second['item']->id)->count();

        if ($firstVotes === $secondVotes) {
            return null;
        }

        $ordered = $firstVotes > $secondVotes ? [$first, $second] : [$second, $first];

        return collect($ordered)
            ->map(fn (array $entry) => [...$entry, 'tiebreaker' => 'head_to_head']);
    }
}
