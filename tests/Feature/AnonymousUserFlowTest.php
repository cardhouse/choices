<?php

namespace Tests\Feature;

use App\Livewire\Auth\Register;
use App\Livewire\List\CreateList;
use App\Livewire\List\VoteRound;
use App\Livewire\List\RankedResults;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class AnonymousUserFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_anonymous_user_flow(): void
    {
        // Start the session
        $this->session([]);
        
        // Step 1: Visit homepage and create list as guest
        $component = Livewire::test(CreateList::class)
            ->set('title', 'Test Anonymous List')
            ->set('description', 'Test Description')
            ->set('items', ['Item 1', 'Item 2', 'Item 3', 'Item 4'])
            ->call('createList');

        // Assert list was created and user was redirected
        $list = DecisionList::first();
        $this->assertNotNull($list);
        $this->assertTrue($list->is_anonymous);
        $this->assertNull($list->user_id);
        $component->assertRedirect(route('lists.show', ['list' => $list]));

        // Step 2: Start voting on the list
        $component = Livewire::test(VoteRound::class, ['list' => $list]);
        
        // Calculate expected number of matchups (n(n-1)/2)
        $expectedMatchups = (4 * (4 - 1)) / 2; // 6 matchups for 4 items
        
        // Step 3: Complete all matchups
        for ($i = 1; $i <= $expectedMatchups; $i++) {
            // Get the current matchup
            $currentMatchupId = $component->get('currentMatchup.id');
            $currentMatchup = Matchup::find($currentMatchupId);
            
            $this->assertNotNull($currentMatchup, "Failed to get current matchup for vote {$i}");
            $this->assertEquals('pending', $currentMatchup->status, "Expected matchup to be pending for vote {$i}");
            
            // Vote for item_a
            $component->call('vote', $currentMatchup->item_a_id);
            
            // Verify the vote was recorded with session token
            $this->assertDatabaseHas('votes', [
                'matchup_id' => $currentMatchup->id,
                'chosen_item_id' => $currentMatchup->item_a_id,
                'user_id' => null,
                'session_token' => session()->getId(),
            ]);
        }

        // Step 4: Verify session data is set correctly
        $this->assertEquals(route('lists.results', ['list' => $list]), session('intended_url'));
        $this->assertEquals($list->id, session('anonymous_list_id'));
        $this->assertEquals(
            'Please register or login to view your voting results. Your votes have been saved and will be available after registration.',
            session('message')
        );

        // Step 5: Register a new user
        $component = Livewire::test(Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        // Verify list is claimed by user
        $list->refresh();
        $this->assertEquals(User::where('email', 'test@example.com')->first()->id, $list->user_id);
        $this->assertFalse($list->is_anonymous);
        $this->assertNotNull($list->claimed_at);

        // Verify list appears in user's dashboard
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee($list->title);

        // Step 6: Verify results access
        $this->get(route('lists.results', ['list' => $list]))
            ->assertOk()
            ->assertSee('Results');

        // Step 7: Verify results are displayed correctly
        $component = Livewire::test(RankedResults::class, ['list' => $list])
            ->assertSee('Results')
            ->assertSee('See how your items performed in head-to-head voting')
            ->assertSee('Item 1')
            ->assertSee('Item 2')
            ->assertSee('Item 3')
            ->assertSee('Item 4')
            ->assertSee('🥇')
            ->assertSee('2')
            ->assertSee('3')
            ->assertSee('4');
    }
} 