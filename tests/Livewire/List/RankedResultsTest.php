<?php

namespace Tests\Livewire\List;

use App\Livewire\List\RankedResults;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use Livewire\Livewire;
use Tests\TestCase;

class RankedResultsTest extends TestCase
{
    public function test_displays_ranked_items_with_medals()
    {
        $user = User::factory()->create();
        $list = DecisionList::factory()->create([
            'user_id' => $user->id,
            'voting_completed_at' => now(),
        ]);
        
        $item1 = DecisionListItem::factory()->create(['list_id' => $list->id]);
        $item2 = DecisionListItem::factory()->create(['list_id' => $list->id]);
        $item3 = DecisionListItem::factory()->create(['list_id' => $list->id]);
        
        $matchup1 = Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $item1->id,
            'item_b_id' => $item2->id,
            'winner_item_id' => $item1->id,
            'status' => 'completed',
        ]);
        
        $matchup2 = Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $item1->id,
            'item_b_id' => $item3->id,
            'winner_item_id' => $item1->id,
            'status' => 'completed',
        ]);
        
        Vote::factory()->create([
            'matchup_id' => $matchup1->id,
            'user_id' => $user->id,
            'chosen_item_id' => $item1->id,
            'session_token' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
        ]);
        
        Vote::factory()->create([
            'matchup_id' => $matchup2->id,
            'user_id' => $user->id,
            'chosen_item_id' => $item1->id,
            'session_token' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
        ]);
        
        Livewire::test(RankedResults::class, ['list' => $list])
            ->assertSee('Results')
            ->assertSee('See how your items performed in head-to-head voting')
            ->assertSee($item1->label)
            ->assertSee($item2->label)
            ->assertSee($item3->label)
            ->assertSee('🥇')
            ->assertSee('2');
    }
} 