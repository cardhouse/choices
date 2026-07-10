<?php

namespace Tests\Feature;

use App\Actions\Lists\CreateList;
use App\Livewire\List\JoinList;
use App\Livewire\List\RankedResults;
use App\Livewire\List\ShareList;
use App\Livewire\List\VoteRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VotingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Vote item_a on every remaining matchup for the given component.
     */
    private function voteThroughAll($component): void
    {
        while ($component->get('currentMatchup') !== null) {
            $component->call('vote', $component->get('currentMatchup.item_a_id'));
        }
    }

    public function test_solo_voting_flow_ends_at_results(): void
    {
        $user = User::factory()->create();

        $list = app(CreateList::class)->handle([
            'title' => 'Test Voting List',
            'items' => ['Item 1', 'Item 2', 'Item 3'],
        ], $user);

        $component = Livewire::actingAs($user)
            ->test(VoteRound::class, ['list' => $list]);

        $this->voteThroughAll($component);

        // Solo list closes itself when its only voter finishes
        $this->assertTrue($list->fresh()->isVotingClosed());
        $component->assertRedirect(route('lists.results', ['list' => $list]));

        $results = Livewire::actingAs($user)
            ->test(RankedResults::class, ['list' => $list->fresh()])
            ->assertSee('Rank')
            ->assertSee('Victories')
            ->assertSee('Winner');

        foreach (['Item 1', 'Item 2', 'Item 3'] as $label) {
            $results->assertSee($label);
        }
    }

    public function test_shared_voting_flow_with_two_voters(): void
    {
        $owner = User::factory()->create();
        $friend = User::factory()->create();

        $list = app(CreateList::class)->handle([
            'title' => 'Movie Night',
            'items' => ['Alien', 'Blade Runner', 'Casablanca'],
        ], $owner);

        // Owner shares the list
        $share = Livewire::actingAs($owner)
            ->test(ShareList::class, ['list' => $list])
            ->call('generateCode');

        $code = $list->fresh()->activeShareCode();
        $this->assertNotNull($code);

        // Friend joins with the code and is redirected to vote
        Livewire::actingAs($friend)
            ->test(JoinList::class, ['code' => $code->code])
            ->assertRedirect(route('lists.vote', ['list' => $list]));

        $this->assertTrue($list->fresh()->hasParticipant($friend));

        // Both voters vote on all matchups
        $ownerVoting = Livewire::actingAs($owner)->test(VoteRound::class, ['list' => $list]);
        $this->voteThroughAll($ownerVoting);

        // Owner finishing a shared list does not close it
        $this->assertFalse($list->fresh()->isVotingClosed());

        $friendVoting = Livewire::actingAs($friend)->test(VoteRound::class, ['list' => $list]);
        $this->voteThroughAll($friendVoting);
        $friendVoting->assertNoRedirect();

        // Every matchup collected one vote per voter
        $this->assertEquals(6, $list->votes()->count());

        // Friend cannot see results while voting is open
        Livewire::actingAs($friend)
            ->test(RankedResults::class, ['list' => $list->fresh()])
            ->assertStatus(403);

        // Owner closes voting; the code deactivates and results open up
        Livewire::actingAs($owner)
            ->test(ShareList::class, ['list' => $list->fresh()])
            ->call('closeVoting')
            ->assertRedirect(route('lists.results', ['list' => $list]));

        $this->assertTrue($list->fresh()->isVotingClosed());
        $this->assertFalse($list->fresh()->isShared());

        Livewire::actingAs($friend)
            ->test(RankedResults::class, ['list' => $list->fresh()])
            ->assertSee('Victories')
            ->assertSee('Winner');

        // Both voters picked item_a every time, so item_a of each matchup
        // accumulated two votes; the overall winner has 4 victories.
        $owner = Livewire::actingAs($owner)
            ->test(RankedResults::class, ['list' => $list->fresh()])
            ->assertSee('2 voters participated');
    }

    public function test_share_codes_cannot_be_redeemed_after_close(): void
    {
        $owner = User::factory()->create();
        $friend = User::factory()->create();

        $list = app(CreateList::class)->handle([
            'title' => 'Closed List',
            'items' => ['A', 'B'],
        ], $owner);

        Livewire::actingAs($owner)
            ->test(ShareList::class, ['list' => $list])
            ->call('generateCode');

        $code = $list->fresh()->activeShareCode()->code;

        Livewire::actingAs($owner)
            ->test(ShareList::class, ['list' => $list->fresh()])
            ->call('closeVoting');

        Livewire::actingAs($friend)
            ->test(JoinList::class)
            ->set('code', $code)
            ->call('join')
            ->assertHasErrors(['code']);

        $this->assertFalse($list->fresh()->hasParticipant($friend));
    }

    public function test_deadline_expiry_closes_voting(): void
    {
        $owner = User::factory()->create();

        $list = app(CreateList::class)->handle([
            'title' => 'Deadline List',
            'items' => ['A', 'B'],
        ], $owner);

        Livewire::actingAs($owner)
            ->test(ShareList::class, ['list' => $list])
            ->set('duration', '1day')
            ->call('generateCode');

        $this->assertNotNull($list->fresh()->voting_closes_at);
        $this->assertTrue($list->fresh()->isVotingOpen());

        $this->travel(2)->days();

        $this->assertTrue($list->fresh()->isVotingClosed());

        // Voting attempts are turned away
        Livewire::actingAs($owner)
            ->test(VoteRound::class, ['list' => $list->fresh()])
            ->assertRedirect(route('lists.results', ['list' => $list]));
    }
}
