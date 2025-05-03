<?php

namespace Tests\Livewire\List;

use App\Livewire\List\VoteRound;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class VoteRoundTest extends \Tests\TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_renders_successfully()
    {
        $list = DecisionList::factory()->create();
        $items = DecisionListItem::factory()->count(3)->create(['list_id' => $list->id]);
        Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[0]->id,
            'item_b_id' => $items[1]->id,
        ]);

        Livewire::test(VoteRound::class, ['list' => $list])
            ->assertStatus(200);
    }

    #[Test]
    public function it_loads_next_matchup()
    {
        $list = DecisionList::factory()->create();
        $items = DecisionListItem::factory()->count(3)->create(['list_id' => $list->id]);
        $matchup = Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[0]->id,
            'item_b_id' => $items[1]->id,
        ]);

        $component = Livewire::test(VoteRound::class, ['list' => $list]);
        
        $this->assertEquals($matchup->id, $component->get('currentMatchup.id'));
        $this->assertEquals(3, $component->get('totalMatchups'));
        $this->assertEquals(0, $component->get('completedMatchups'));
        $this->assertEquals(0, $component->get('progress'));
    }

    #[Test]
    public function it_handles_voting_for_authenticated_user()
    {
        $user = User::factory()->create();
        $list = DecisionList::factory()->create();
        $items = DecisionListItem::factory()->count(3)->create(['list_id' => $list->id]);
        $matchup = Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[0]->id,
            'item_b_id' => $items[1]->id,
        ]);

        $component = Livewire::actingAs($user)
            ->test(VoteRound::class, ['list' => $list]);

        $component->call('vote', $items[0]->id);
        
        $this->assertEquals($items[0]->id, $matchup->fresh()->winner_item_id);
        $this->assertEquals(1, $component->get('completedMatchups'));
        $this->assertEquals(33.33, $component->get('progress'));
    }

    #[Test]
    public function it_handles_voting_for_anonymous_user()
    {
        $list = DecisionList::factory()->create();
        $items = DecisionListItem::factory()->count(3)->create(['list_id' => $list->id]);
        $matchup = Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[0]->id,
            'item_b_id' => $items[1]->id,
        ]);

        $component = Livewire::test(VoteRound::class, ['list' => $list]);

        $component->call('vote', $items[0]->id);
        
        $this->assertEquals($items[0]->id, $matchup->fresh()->winner_item_id);
        $this->assertEquals(1, $component->get('completedMatchups'));
        $this->assertEquals(33.33, $component->get('progress'));
    }

    #[Test]
    public function it_completes_all_matchups()
    {
        $list = DecisionList::factory()->create();
        $items = DecisionListItem::factory()->count(3)->create(['list_id' => $list->id]);
        
        // Create all possible matchups
        Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[0]->id,
            'item_b_id' => $items[1]->id,
        ]);
        Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[0]->id,
            'item_b_id' => $items[2]->id,
        ]);
        Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[1]->id,
            'item_b_id' => $items[2]->id,
        ]);

        $component = Livewire::test(VoteRound::class, ['list' => $list]);

        // Vote on all matchups
        $component->call('vote', $items[0]->id);
        $this->assertEquals(1, $component->get('completedMatchups'));
        $this->assertEquals(33.33, $component->get('progress'));

        $component->call('vote', $items[0]->id);
        $this->assertEquals(2, $component->get('completedMatchups'));
        $this->assertEquals(66.67, $component->get('progress'));

        $component->call('vote', $items[1]->id);
        $this->assertEquals(3, $component->get('completedMatchups'));
        $this->assertEquals(100, $component->get('progress'));
    }

    #[Test]
    public function it_validates_vote_choice()
    {
        $list = DecisionList::factory()->create();
        $items = DecisionListItem::factory()->count(3)->create(['list_id' => $list->id]);
        $matchup = Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[0]->id,
            'item_b_id' => $items[1]->id,
        ]);

        // Try to vote for an item not in the current matchup
        Livewire::test(VoteRound::class, ['list' => $list])
            ->call('vote', $items[2]->id)
            ->assertHasErrors(['vote']);
    }
} 