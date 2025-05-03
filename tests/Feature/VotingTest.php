<?php

namespace Tests\Feature;

use App\Livewire\List\VoteRound;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VotingTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_voting_flow(): void
    {
        // Create a test user
        $user = User::factory()->create();
        
        // Create a test list with items
        $list = DecisionList::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Voting List',
        ]);
        
        // Add items to the list
        $items = [
            'Item 1',
            'Item 2',
            'Item 3',
        ];
        
        $createdItems = [];
        foreach ($items as $item) {
            $createdItems[] = DecisionListItem::create([
                'list_id' => $list->id,
                'label' => $item,
            ]);
        }

        // Create all possible matchups
        for ($i = 0; $i < count($createdItems); $i++) {
            for ($j = $i + 1; $j < count($createdItems); $j++) {
                \App\Models\Matchup::create([
                    'list_id' => $list->id,
                    'item_a_id' => $createdItems[$i]->id,
                    'item_b_id' => $createdItems[$j]->id,
                    'status' => 'pending',
                ]);
            }
        }

        // Calculate expected number of matchups (n(n-1)/2)
        $expectedMatchups = (count($items) * (count($items) - 1)) / 2;
        
        // Initialize the VoteRound component
        $component = Livewire::actingAs($user)
            ->test(VoteRound::class, ['list' => $list]);
        
        // Vote on all matchups
        for ($i = 1; $i <= $expectedMatchups; $i++) {
            // Get the current matchup from the component
            $currentMatchupId = $component->get('currentMatchup.id');
            $currentMatchup = \App\Models\Matchup::find($currentMatchupId);
            
            $this->assertNotNull($currentMatchup, "Failed to get current matchup from component for round {$i}");
            $this->assertEquals('pending', $currentMatchup->status, "Expected matchup to be pending for round {$i}");
            
            // Vote for the left item (item_a)
            $component->call('vote', $currentMatchup->item_a_id)
                ->assertOk();
        }

        // Test the results view
        $component = Livewire::test(\App\Livewire\List\RankedResults::class, ['list' => $list])
            ->assertSee('Rank')
            ->assertSee('Item')
            ->assertSee('Wins');
        
        // Verify each item is present
        foreach ($items as $item) {
            $component->assertSee($item);
        }
    }
    
    public function test_anonymous_voting_flow(): void
    {
        // Create a test list with items
        $list = DecisionList::factory()->create([
            'title' => 'Anonymous Test List',
        ]);
        
        // Add items to the list
        $items = [
            'Item A',
            'Item B',
            'Item C',
        ];
        
        $createdItems = [];
        foreach ($items as $item) {
            $createdItems[] = DecisionListItem::create([
                'list_id' => $list->id,
                'label' => $item,
            ]);
        }

        // Create all possible matchups
        for ($i = 0; $i < count($createdItems); $i++) {
            for ($j = $i + 1; $j < count($createdItems); $j++) {
                \App\Models\Matchup::create([
                    'list_id' => $list->id,
                    'item_a_id' => $createdItems[$i]->id,
                    'item_b_id' => $createdItems[$j]->id,
                    'status' => 'pending',
                ]);
            }
        }

        // Test the VoteRound component
        $component = Livewire::test(VoteRound::class, ['list' => $list]);
            
        // Get the current matchup from the component
        $currentMatchupId = $component->get('currentMatchup.id');
        $currentMatchup = \App\Models\Matchup::find($currentMatchupId);
        
        $component->assertSee('Which do you prefer?')
            ->assertSee('Item A')
            ->assertSee('Item B');

        // Calculate expected number of matchups
        $expectedMatchups = (count($items) * (count($items) - 1)) / 2;
        
        // Vote on all matchups
        for ($i = 1; $i <= $expectedMatchups; $i++) {
            // Get the current matchup from the component
            $currentMatchupId = $component->get('currentMatchup.id');
            $currentMatchup = \App\Models\Matchup::find($currentMatchupId);
            
            $this->assertNotNull($currentMatchup, "Failed to get current matchup from component for round {$i}");
            $this->assertEquals('pending', $currentMatchup->status, "Expected matchup to be pending for round {$i}");
            
            // Vote for the left item (item_a)
            $component->call('vote', $currentMatchup->item_a_id)
                ->assertOk();
        }

        // Test the results view
        $component = Livewire::test(\App\Livewire\List\RankedResults::class, ['list' => $list])
            ->assertSee('Rank')
            ->assertSee('Item')
            ->assertSee('Wins');
        
        // Verify each item is present
        foreach ($items as $item) {
            $component->assertSee($item);
        }
    }

    public function test_five_item_voting_flow_with_vote_counts(): void
    {
        // Create a test user
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        
        // Create a test list with items
        $list = DecisionList::factory()->create([
            'user_id' => $user->id,
            'title' => 'Five Item Voting Test',
        ]);
        
        // Add 5 items to the list
        $items = [
            'Item 1',
            'Item 2',
            'Item 3',
            'Item 4',
            'Item 5',
        ];
        
        $createdItems = [];
        foreach ($items as $item) {
            $createdItems[] = DecisionListItem::create([
                'list_id' => $list->id,
                'label' => $item,
            ]);
        }

        // Create all possible matchups
        for ($i = 0; $i < count($createdItems); $i++) {
            for ($j = $i + 1; $j < count($createdItems); $j++) {
                \App\Models\Matchup::create([
                    'list_id' => $list->id,
                    'item_a_id' => $createdItems[$i]->id,
                    'item_b_id' => $createdItems[$j]->id,
                    'status' => 'pending',
                ]);
            }
        }

        // Calculate expected number of matchups (n(n-1)/2)
        $expectedMatchups = (count($items) * (count($items) - 1)) / 2;
        
        // Vote on all matchups
        for ($i = 1; $i <= $expectedMatchups; $i++) {
            // Initialize the VoteRound component
            $component = Livewire::actingAs($user)
                ->test(VoteRound::class, ['list' => $list]);
            
            // Get the current matchup from the component
            $currentMatchupId = $component->get('currentMatchup.id');
            $currentMatchup = \App\Models\Matchup::find($currentMatchupId);
            
            $this->assertNotNull($currentMatchup, "Failed to get current matchup from component for round {$i}");
            $this->assertEquals('pending', $currentMatchup->status, "Expected matchup to be pending for round {$i}");
            
            // Vote for the left item (item_a)
            $component->call('vote', $currentMatchup->item_a_id)
                ->assertOk();
                
            // Debug the component state
            $this->assertDatabaseHas('votes', [
                'matchup_id' => $currentMatchup->id,
                'chosen_item_id' => $currentMatchup->item_a_id,
            ]);
            
            // Verify the matchup was marked as completed
            $updatedMatchup = \App\Models\Matchup::find($currentMatchup->id);
            $this->assertNotNull($updatedMatchup, "Failed to find matchup after voting");
            $this->assertEquals('completed', $updatedMatchup->status, 
                "Matchup {$i} status is '{$updatedMatchup->status}' instead of 'completed' after voting");
        }

        // Verify all matchups are completed
        $this->assertEquals(
            0,
            \App\Models\Matchup::where('list_id', $list->id)
                ->where('status', 'pending')
                ->count(),
            "There should be no pending matchups after voting"
        );

        // Test the results view
        $component = Livewire::test(\App\Livewire\List\RankedResults::class, ['list' => $list])
            ->assertSee('Rank')
            ->assertSee('Item')
            ->assertSee('Wins');
        
        // Verify each item is present
        foreach ($items as $item) {
            $component->assertSee($item);
        }
        
        // Verify vote counts for each item that was on the left side
        $expectedLeftSideWins = [];
        foreach ($createdItems as $index => $item) {
            $expectedLeftSideWins[$item->id] = count($createdItems) - $index - 1;
        }
        
        foreach ($expectedLeftSideWins as $itemId => $expectedWins) {
            $actualWins = \App\Models\Vote::where('chosen_item_id', $itemId)->count();
            $this->assertEquals(
                $expectedWins,
                $actualWins,
                "Item {$itemId} should have {$expectedWins} wins as it was on the left side"
            );
        }
    }
} 