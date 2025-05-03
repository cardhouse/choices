<?php

namespace Tests\Livewire\List;

use App\Livewire\List\ResultsMatrix;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use Livewire\Livewire;
use Tests\TestCase;

class ResultsMatrixTest extends TestCase
{
    public function test_displays_matrix_with_results()
    {
        $user = User::factory()->create();
        $list = DecisionList::factory()->create([
            'user_id' => $user->id,
            'voting_completed_at' => now(),
        ]);
        
        $item1 = DecisionListItem::factory()->create(['list_id' => $list->id]);
        $item2 = DecisionListItem::factory()->create(['list_id' => $list->id]);
        $item3 = DecisionListItem::factory()->create(['list_id' => $list->id]);
        
        $matchup = Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $item1->id,
            'item_b_id' => $item2->id,
            'winner_item_id' => $item1->id,
            'status' => 'completed',
        ]);
        
        Vote::factory()->create([
            'matchup_id' => $matchup->id,
            'user_id' => $user->id,
            'chosen_item_id' => $item1->id,
            'session_token' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
        ]);
        
        Livewire::test(ResultsMatrix::class, ['list' => $list])
            ->assertSee($item1->label)
            ->assertSee($item2->label)
            ->assertSee($item3->label)
            ->assertSee('✅')
            ->assertSee('❌')
            ->assertSee('-');
    }

    public function test_displays_vote_counts_when_enabled()
    {
        $user = User::factory()->create();
        $list = DecisionList::factory()->create([
            'user_id' => $user->id,
            'voting_completed_at' => now(),
        ]);
        
        $item1 = DecisionListItem::factory()->create(['list_id' => $list->id]);
        $item2 = DecisionListItem::factory()->create(['list_id' => $list->id]);
        
        $matchup = Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $item1->id,
            'item_b_id' => $item2->id,
            'winner_item_id' => $item1->id,
            'status' => 'completed',
        ]);
        
        Vote::factory()->create([
            'matchup_id' => $matchup->id,
            'user_id' => $user->id,
            'chosen_item_id' => $item1->id,
            'session_token' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
        ]);
        
        Livewire::test(ResultsMatrix::class, ['list' => $list, 'showVoteCounts' => true])
            ->assertSee('✅ (1)')
            ->assertSee('❌ (1)');
    }
} 