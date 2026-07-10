<?php

namespace Tests\Livewire\List;

use App\Livewire\List\RankedResults;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\ListParticipant;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use Livewire\Livewire;
use Tests\TestCase;

class RankedResultsTest extends TestCase
{
    /**
     * A closed list where item1 beat item2 and item3 (2 victories).
     *
     * @return array{0: DecisionList, 1: DecisionListItem, 2: DecisionListItem, 3: DecisionListItem}
     */
    private function makeVotedList(): array
    {
        $list = DecisionList::factory()->closed()->create();

        [$item1, $item2, $item3] = DecisionListItem::factory()->count(3)->create(['list_id' => $list->id]);

        foreach ([$item2, $item3] as $loser) {
            $matchup = Matchup::factory()->create([
                'list_id' => $list->id,
                'item_a_id' => $item1->id,
                'item_b_id' => $loser->id,
            ]);

            Vote::create([
                'matchup_id' => $matchup->id,
                'user_id' => $list->user_id,
                'chosen_item_id' => $item1->id,
            ]);
        }

        return [$list, $item1, $item2, $item3];
    }

    public function test_owner_sees_ranked_items_with_winner_and_analytics()
    {
        [$list, $item1, $item2, $item3] = $this->makeVotedList();

        Livewire::actingAs($list->user)
            ->test(RankedResults::class, ['list' => $list])
            ->assertSee('Results')
            ->assertSee($item1->label)
            ->assertSee($item2->label)
            ->assertSee($item3->label)
            ->assertSee('🥇')
            ->assertSee('Winner')
            ->assertSee('voter participated')
            ->assertSee('Head-to-Head Breakdown');
    }

    public function test_participant_sees_rankings_but_not_analytics()
    {
        [$list, $item1] = $this->makeVotedList();

        $friend = User::factory()->create();
        ListParticipant::factory()->create(['list_id' => $list->id, 'user_id' => $friend->id]);

        Livewire::actingAs($friend)
            ->test(RankedResults::class, ['list' => $list])
            ->assertSee($item1->label)
            ->assertDontSee('Head-to-Head Breakdown')
            ->assertDontSee('voter participated');
    }

    public function test_participant_cannot_view_results_while_voting_is_open()
    {
        $list = DecisionList::factory()->create();
        $friend = User::factory()->create();
        ListParticipant::factory()->create(['list_id' => $list->id, 'user_id' => $friend->id]);

        Livewire::actingAs($friend)
            ->test(RankedResults::class, ['list' => $list])
            ->assertStatus(403);
    }

    public function test_stranger_cannot_view_results()
    {
        [$list] = $this->makeVotedList();

        Livewire::actingAs(User::factory()->create())
            ->test(RankedResults::class, ['list' => $list])
            ->assertStatus(403);
    }
}
