<?php

namespace Tests\Services;

use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use App\Services\ScoreCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ScoreCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private ScoreCalculator $calculator;

    private DecisionList $list;

    /** @var Collection<int, DecisionListItem> */
    private Collection $items;

    /** @var array<string, Matchup> matchups keyed by "i-j" item index */
    private array $matchups = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new ScoreCalculator;

        $this->list = DecisionList::factory()->create();
        $this->items = DecisionListItem::factory()->count(3)->create(['list_id' => $this->list->id]);

        foreach ([[0, 1], [0, 2], [1, 2]] as [$i, $j]) {
            $this->matchups["$i-$j"] = Matchup::factory()->create([
                'list_id' => $this->list->id,
                'item_a_id' => $this->items[$i]->id,
                'item_b_id' => $this->items[$j]->id,
            ]);
        }
    }

    /**
     * Cast one voter's vote on a matchup.
     */
    private function vote(string $matchupKey, int $winnerIndex, User $voter): void
    {
        Vote::create([
            'matchup_id' => $this->matchups[$matchupKey]->id,
            'user_id' => $voter->id,
            'chosen_item_id' => $this->items[$winnerIndex]->id,
        ]);
    }

    public function test_ranks_items_by_total_votes_across_voters(): void
    {
        [$alice, $bob] = User::factory()->count(2)->create();

        // Alice: 0 beats 1, 0 beats 2, 1 beats 2  → 0:2, 1:1, 2:0
        $this->vote('0-1', 0, $alice);
        $this->vote('0-2', 0, $alice);
        $this->vote('1-2', 1, $alice);

        // Bob: 0 beats 1, 2 beats 0, 1 beats 2    → 0:1, 1:1, 2:1
        $this->vote('0-1', 0, $bob);
        $this->vote('0-2', 2, $bob);
        $this->vote('1-2', 1, $bob);

        $results = $this->calculator->forList($this->list);

        // Totals: item0 = 3, item1 = 2, item2 = 1
        $this->assertEquals($this->items[0]->id, $results[0]['item']->id);
        $this->assertEquals(3, $results[0]['score']);
        $this->assertEquals(1, $results[0]['rank']);
        $this->assertNull($results[0]['tiebreaker']);

        $this->assertEquals($this->items[1]->id, $results[1]['item']->id);
        $this->assertEquals(2, $results[1]['score']);

        $this->assertEquals($this->items[2]->id, $results[2]['item']->id);
        $this->assertEquals(1, $results[2]['score']);
        $this->assertEquals(3, $results[2]['rank']);
    }

    public function test_head_to_head_tiebreaker_orders_two_way_tie(): void
    {
        $alice = User::factory()->create();

        // Single voter: 1 beats 0, 0 beats 2, 1 beats 2 → item1: 2, item0: 1, item2: 0
        // Then give item0 an extra victory via a second voter on 0-2 only.
        $this->vote('0-1', 1, $alice);
        $this->vote('0-2', 0, $alice);
        $this->vote('1-2', 1, $alice);

        $bob = User::factory()->create();
        $this->vote('0-2', 0, $bob);

        // Totals: item0 = 2, item1 = 2, item2 = 0. Head-to-head (0-1) went to
        // item1, so item1 is ranked first with the head_to_head flag.
        $results = $this->calculator->forList($this->list);

        $this->assertEquals($this->items[1]->id, $results[0]['item']->id);
        $this->assertEquals('head_to_head', $results[0]['tiebreaker']);
        $this->assertEquals($this->items[0]->id, $results[1]['item']->id);
        $this->assertEquals('head_to_head', $results[1]['tiebreaker']);
        $this->assertEquals(1, $results[0]['rank']);
        $this->assertEquals(2, $results[1]['rank']);
    }

    public function test_random_tiebreak_is_stable_per_list(): void
    {
        $alice = User::factory()->create();

        // A cycle: 0 beats 1, 1 beats 2, 2 beats 0 → all tied at 1, and every
        // head-to-head is decided, but it's a 3-way tie → random.
        $this->vote('0-1', 0, $alice);
        $this->vote('1-2', 1, $alice);
        $this->vote('0-2', 2, $alice);

        $first = $this->calculator->forList($this->list);
        $second = $this->calculator->forList($this->list);

        $this->assertEquals(
            $first->pluck('item.id')->all(),
            $second->pluck('item.id')->all(),
        );
        $this->assertTrue($first->every(fn ($entry) => $entry['tiebreaker'] === 'random'));
    }

    public function test_items_with_no_votes_score_zero(): void
    {
        $results = $this->calculator->forList($this->list);

        $this->assertCount(3, $results);
        $this->assertTrue($results->every(fn ($entry) => $entry['score'] === 0));
    }

    public function test_counts_distinct_voters(): void
    {
        [$alice, $bob] = User::factory()->count(2)->create();

        $this->vote('0-1', 0, $alice);
        $this->vote('0-2', 0, $alice);
        $this->vote('0-1', 1, $bob);

        Vote::create([
            'matchup_id' => $this->matchups['1-2']->id,
            'user_id' => null,
            'session_token' => 'guest-token',
            'chosen_item_id' => $this->items[1]->id,
        ]);

        $this->assertEquals(3, $this->calculator->voterCountForList($this->list));
    }
}
