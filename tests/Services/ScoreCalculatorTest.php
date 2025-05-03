<?php

namespace Tests\Services;

use App\Models\DecisionList;
use App\Models\Item;
use App\Models\Matchup;
use App\Services\ScoreCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoreCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private ScoreCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new ScoreCalculator();
    }

    /**
     * Test basic score calculation with no ties
     */
    public function test_calculates_scores_without_ties(): void
    {
        // Create a list with 3 items
        $list = DecisionList::factory()->create();
        $items = Item::factory()->count(3)->create(['list_id' => $list->id]);

        // Create matchups where item 1 wins all, item 2 wins one, item 3 loses all
        Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[0]->id,
            'item_b_id' => $items[1]->id,
            'winner_item_id' => $items[0]->id,
        ]);

        Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[0]->id,
            'item_b_id' => $items[2]->id,
            'winner_item_id' => $items[0]->id,
        ]);

        Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[1]->id,
            'item_b_id' => $items[2]->id,
            'winner_item_id' => $items[1]->id,
        ]);

        $results = $this->calculator->forList($list);

        $this->assertCount(3, $results);
        
        // Verify scores and rankings
        $this->assertEquals(2, $results[0]['score']); // Item 1: 2 wins
        $this->assertEquals(1, $results[0]['rank']);
        
        $this->assertEquals(1, $results[1]['score']); // Item 2: 1 win
        $this->assertEquals(2, $results[1]['rank']);
        
        $this->assertEquals(0, $results[2]['score']); // Item 3: 0 wins
        $this->assertEquals(3, $results[2]['rank']);
    }

    /**
     * Test score calculation with ties resolved by label
     */
    public function test_resolves_ties_by_label(): void
    {
        $list = DecisionList::factory()->create();
        
        // Create items with specific labels to test tiebreaker
        $items = collect([
            Item::factory()->create(['list_id' => $list->id, 'label' => 'Banana']),
            Item::factory()->create(['list_id' => $list->id, 'label' => 'Apple']),
            Item::factory()->create(['list_id' => $list->id, 'label' => 'Cherry']),
        ]);

        // Create matchups where all items have 1 win
        Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[0]->id,
            'item_b_id' => $items[1]->id,
            'winner_item_id' => $items[0]->id,
        ]);

        Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[1]->id,
            'item_b_id' => $items[2]->id,
            'winner_item_id' => $items[1]->id,
        ]);

        Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[0]->id,
            'item_b_id' => $items[2]->id,
            'winner_item_id' => $items[2]->id,
        ]);

        $results = $this->calculator->forList($list);

        // Verify items are ranked by label (alphabetically) when scores are tied
        $this->assertEquals('Apple', $results[0]['item']->label);
        $this->assertEquals('Banana', $results[1]['item']->label);
        $this->assertEquals('Cherry', $results[2]['item']->label);
        
        // All items should have the same rank since they're tied
        $this->assertEquals(1, $results[0]['rank']);
        $this->assertEquals(1, $results[1]['rank']);
        $this->assertEquals(1, $results[2]['rank']);
    }

    /**
     * Test handling of incomplete matchups
     */
    public function test_handles_incomplete_matchups(): void
    {
        $list = DecisionList::factory()->create();
        $items = Item::factory()->count(2)->create(['list_id' => $list->id]);

        // Create a matchup without a winner
        Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[0]->id,
            'item_b_id' => $items[1]->id,
            'winner_item_id' => null,
        ]);

        $results = $this->calculator->forList($list);

        // Both items should have 0 wins
        $this->assertEquals(0, $results[0]['score']);
        $this->assertEquals(0, $results[1]['score']);
    }

    /**
     * Test error handling for invalid list
     */
    public function test_handles_invalid_list(): void
    {
        $this->expectException(\RuntimeException::class);
        
        // Create a list but don't save it
        $list = new DecisionList();
        
        $this->calculator->forList($list);
    }
} 