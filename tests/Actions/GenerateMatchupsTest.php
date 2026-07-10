<?php

namespace Tests\Actions;

use App\Actions\Lists\GenerateMatchups;
use App\Exceptions\InvalidListException;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateMatchupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_all_unique_pairings(): void
    {
        $list = DecisionList::factory()->create();
        DecisionListItem::factory()->count(4)->create(['list_id' => $list->id]);

        (new GenerateMatchups)->handle($list);

        // n(n-1)/2 pairings for 4 items
        $this->assertEquals(6, $list->matchups()->count());

        // No item paired against itself, no duplicate pairs
        $pairs = $list->matchups()->get()->map(
            fn ($m) => collect([$m->item_a_id, $m->item_b_id])->sort()->implode('-')
        );
        $this->assertEquals($pairs->count(), $pairs->unique()->count());
    }

    public function test_throws_for_list_with_fewer_than_two_items(): void
    {
        $list = DecisionList::factory()->create();
        DecisionListItem::factory()->create(['list_id' => $list->id]);

        $this->expectException(InvalidListException::class);

        (new GenerateMatchups)->handle($list);
    }
}
