<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\List\CreateList;
use App\Livewire\List\RankedResults;
use App\Livewire\List\VoteRound;
use App\Models\DecisionList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AnonymousUserFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create an anonymous list as a guest and vote it to completion.
     * Returns the list, now closed, with the session primed for claiming.
     */
    private function createAndVoteAsGuest(): DecisionList
    {
        $component = Livewire::test(CreateList::class)
            ->set('title', 'Test Anonymous List')
            ->set('description', 'Test Description')
            ->set('items', ['Item 1', 'Item 2', 'Item 3', 'Item 4'])
            ->call('createList');

        $list = DecisionList::first();
        $this->assertNotNull($list);
        $this->assertTrue($list->is_anonymous);
        $this->assertNull($list->user_id);
        $component->assertRedirect(route('lists.show', ['list' => $list]));

        $voting = Livewire::test(VoteRound::class, ['list' => $list]);

        while ($voting->get('currentMatchup') !== null) {
            $matchupId = $voting->get('currentMatchup.id');
            $chosenId = $voting->get('currentMatchup.item_a_id');

            $voting->call('vote', $chosenId);

            $this->assertDatabaseHas('votes', [
                'matchup_id' => $matchupId,
                'chosen_item_id' => $chosenId,
                'user_id' => null,
                'session_token' => session()->getId(),
            ]);
        }

        $voting->assertRedirect(route('lists.prompt', ['list' => $list]));

        // Session primed for the post-registration claim
        $this->assertEquals($list->id, session('anonymous_list_id'));
        $this->assertEquals(session()->getId(), session('anonymous_session_token'));
        $this->assertEquals(route('lists.results', ['list' => $list]), session('intended_url'));

        // Solo anonymous list closes once its only voter finishes
        $this->assertTrue($list->fresh()->isVotingClosed());

        return $list;
    }

    /**
     * The claim happens in middleware on the first authenticated request,
     * then results become accessible.
     */
    private function assertListClaimedByAndResultsVisible(DecisionList $list, User $user): void
    {
        // First authenticated request triggers the ClaimAnonymousList middleware
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee($list->title);

        $list->refresh();
        $this->assertEquals($user->id, $list->user_id);
        $this->assertFalse($list->is_anonymous);
        $this->assertNotNull($list->claimed_at);

        // The guest's votes now belong to the user
        $this->assertEquals(6, $list->votes()->where('user_id', $user->id)->count());

        $this->get(route('lists.results', ['list' => $list]))
            ->assertOk()
            ->assertSee('Results');

        $results = Livewire::test(RankedResults::class, ['list' => $list])
            ->assertSee('Results')
            ->assertSee('🥇')
            ->assertSee('Winner');

        foreach (['Item 1', 'Item 2', 'Item 3', 'Item 4'] as $label) {
            $results->assertSee($label);
        }
    }

    public function test_anonymous_user_flow_with_registration(): void
    {
        $this->session([]);

        $list = $this->createAndVoteAsGuest();

        $this->get(route('lists.prompt', ['list' => $list]))
            ->assertOk()
            ->assertSee('View Your Results')
            ->assertSee('Create Account')
            ->assertSee('Log in');

        Livewire::test(Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        $this->assertListClaimedByAndResultsVisible($list, $user);
    }

    public function test_anonymous_user_flow_with_login(): void
    {
        $this->session([]);

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $list = $this->createAndVoteAsGuest();

        Livewire::test(Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->call('login');

        $this->assertListClaimedByAndResultsVisible($list, $user);
    }
}
