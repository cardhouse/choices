<?php

namespace Tests\Unit\Services;

use App\Exceptions\InvalidListException;
use App\Models\DecisionList;
use App\Models\Item;
use App\Services\MatchupGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchupGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_matchups_for_list_with_items()
    {
        // Create a list with 3 items
        $list = DecisionList::factory()->create();
        $items = Item::factory()->count(3)->create(['list_id' => $list->id]);

        // Generate matchups
        MatchupGenerator::forList($list);

        // Verify the correct number of matchups were created
        $this->assertEquals(3, $list->matchups->count());
    }

    public function test_throws_exception_for_list_with_less_than_two_items()
    {
        $list = DecisionList::factory()->create();
        Item::factory()->create(['list_id' => $list->id]);

        $this->expectException(InvalidListException::class);
        MatchupGenerator::forList($list);
    }

    // Additional test cases that could be added:
    // - Test that matchups are unique (no duplicates)
    // - Test that matchups are properly ordered (item_a_id < item_b_id)
    // - Test that matchups are created within a transaction
    // - Test that matchups are not created if the list is empty
    // - Test that matchups are not created if the list has invalid items
    // - Test performance with large lists (e.g., 100 items)
}
