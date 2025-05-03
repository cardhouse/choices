<?php

namespace Tests\Browser;

use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class VotingTest extends DuskTestCase
{
    public function test_complete_voting_flow(): void
    {
        $this->browse(function (Browser $browser) {
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
            
            foreach ($items as $item) {
                DecisionListItem::create([
                    'decision_list_id' => $list->id,
                    'label' => $item,
                ]);
            }
            
            // Login as the user
            $browser->loginAs($user)
                   ->visit(route('lists.show', ['list' => $list]))
                   ->assertSee('Test Voting List')
                   ->click('@start-voting')
                   ->assertSee('Which do you prefer?');
            
            // Calculate expected number of matchups (n(n-1)/2)
            $expectedMatchups = (count($items) * (count($items) - 1)) / 2;
            
            // Vote on all matchups
            for ($i = 1; $i <= $expectedMatchups; $i++) {
                $browser->assertSee("Matchup {$i} of {$expectedMatchups}")
                       ->click('button:first-child') // Click the first item in each matchup
                       ->pause(500); // Wait for the vote to process
            }
            
            // Verify we're redirected to results page
            $browser->assertPathIs(route('lists.show', ['list' => $list], false))
                   ->assertSee('Results')
                   ->assertSee('Item 1')
                   ->assertSee('Item 2')
                   ->assertSee('Item 3');
        });
    }
    
    public function test_anonymous_voting_flow(): void
    {
        $this->browse(function (Browser $browser) {
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
            
            foreach ($items as $item) {
                DecisionListItem::create([
                    'decision_list_id' => $list->id,
                    'label' => $item,
                ]);
            }
            
            // Visit the list and start voting
            $browser->visit(route('lists.show', ['list' => $list]))
                   ->assertSee('Anonymous Test List')
                   ->click('@start-voting')
                   ->assertSee('Which do you prefer?');
            
            // Calculate expected number of matchups
            $expectedMatchups = (count($items) * (count($items) - 1)) / 2;
            
            // Vote on all matchups
            for ($i = 1; $i <= $expectedMatchups; $i++) {
                $browser->assertSee("Matchup {$i} of {$expectedMatchups}")
                       ->click('button:first-child') // Click the first item in each matchup
                       ->pause(500); // Wait for the vote to process
            }
            
            // Verify we're redirected to results page
            $browser->assertPathIs(route('lists.show', ['list' => $list], false))
                   ->assertSee('Results')
                   ->assertSee('Item A')
                   ->assertSee('Item B')
                   ->assertSee('Item C');
        });
    }
} 