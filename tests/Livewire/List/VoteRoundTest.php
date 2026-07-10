<?php

namespace Tests\Livewire\List;

use App\Livewire\List\VoteRound;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\ListParticipant;
use App\Models\Matchup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class VoteRoundTest extends \Tests\TestCase
{
    use RefreshDatabase;

    /**
     * Create a list with all round-robin matchups for $itemCount items.
     *
     * @return array{0: DecisionList, 1: \Illuminate\Support\Collection<int, DecisionListItem>}
     */
    private function makeList(int $itemCount = 3, array $attributes = []): array
    {
        $list = DecisionList::factory()->create($attributes);
        $items = DecisionListItem::factory()->count($itemCount)->create(['list_id' => $list->id]);

        for ($i = 0; $i < $itemCount; $i++) {
            for ($j = $i + 1; $j < $itemCount; $j++) {
                Matchup::factory()->create([
                    'list_id' => $list->id,
                    'item_a_id' => $items[$i]->id,
                    'item_b_id' => $items[$j]->id,
                ]);
            }
        }

        return [$list, $items];
    }

    #[Test]
    public function it_renders_for_the_owner()
    {
        [$list] = $this->makeList();

        Livewire::actingAs($list->user)
            ->test(VoteRound::class, ['list' => $list])
            ->assertStatus(200)
            ->assertSee('Which do you prefer?');
    }

    #[Test]
    public function it_renders_for_a_guest_on_an_anonymous_list()
    {
        [$list] = $this->makeList(3, ['user_id' => null, 'is_anonymous' => true]);

        Livewire::test(VoteRound::class, ['list' => $list])
            ->assertStatus(200);
    }

    #[Test]
    public function strangers_are_denied()
    {
        [$list] = $this->makeList();

        Livewire::actingAs(User::factory()->create())
            ->test(VoteRound::class, ['list' => $list])
            ->assertStatus(403);
    }

    #[Test]
    public function it_loads_a_matchup_and_tracks_totals()
    {
        [$list] = $this->makeList();

        $component = Livewire::actingAs($list->user)
            ->test(VoteRound::class, ['list' => $list]);

        $this->assertNotNull($component->get('currentMatchup'));
        $this->assertEquals(3, $component->get('totalMatchups'));
        $this->assertEquals(0, $component->get('completedMatchups'));
        $this->assertEquals(0, $component->get('progress'));
    }

    #[Test]
    public function voting_records_a_vote_without_touching_the_matchup()
    {
        [$list] = $this->makeList();
        $user = $list->user;

        $component = Livewire::actingAs($user)
            ->test(VoteRound::class, ['list' => $list]);

        $matchupId = $component->get('currentMatchup.id');
        $chosenId = $component->get('currentMatchup.item_a_id');

        $component->call('vote', $chosenId);

        $this->assertDatabaseHas('votes', [
            'matchup_id' => $matchupId,
            'user_id' => $user->id,
            'chosen_item_id' => $chosenId,
        ]);
        $this->assertEquals(1, $component->get('completedMatchups'));
        $this->assertEquals(33.33, $component->get('progress'));
    }

    #[Test]
    public function two_voters_progress_independently()
    {
        [$list] = $this->makeList();
        $friend = User::factory()->create();
        ListParticipant::factory()->create(['list_id' => $list->id, 'user_id' => $friend->id]);

        $owner = Livewire::actingAs($list->user)
            ->test(VoteRound::class, ['list' => $list]);
        $owner->call('vote', $owner->get('currentMatchup.item_a_id'));

        // The friend still has all three matchups to vote on.
        $friendComponent = Livewire::actingAs($friend)
            ->test(VoteRound::class, ['list' => $list]);

        $this->assertEquals(0, $friendComponent->get('completedMatchups'));
        $this->assertEquals(3, count($friendComponent->get('matchupQueue')) + 1);
    }

    #[Test]
    public function completing_an_unshared_list_closes_voting_and_shows_results()
    {
        [$list] = $this->makeList();

        $component = Livewire::actingAs($list->user)
            ->test(VoteRound::class, ['list' => $list]);

        while ($component->get('currentMatchup') !== null) {
            $component->call('vote', $component->get('currentMatchup.item_a_id'));
        }

        $this->assertTrue($list->fresh()->isVotingClosed());
        $component->assertRedirect(route('lists.results', ['list' => $list]));
    }

    #[Test]
    public function completing_a_shared_list_leaves_voting_open()
    {
        [$list] = $this->makeList();
        \App\Models\ShareCode::factory()->create(['list_id' => $list->id]);

        $friend = User::factory()->create();
        ListParticipant::factory()->create(['list_id' => $list->id, 'user_id' => $friend->id]);

        $component = Livewire::actingAs($friend)
            ->test(VoteRound::class, ['list' => $list]);

        while ($component->get('currentMatchup') !== null) {
            $component->call('vote', $component->get('currentMatchup.item_a_id'));
        }

        $this->assertFalse($list->fresh()->isVotingClosed());
        $this->assertTrue($component->get('finished'));
        $component->assertNoRedirect();
    }

    #[Test]
    public function it_validates_vote_choice()
    {
        [$list] = $this->makeList();

        // An item that exists but is not part of the presented matchup.
        $outsider = DecisionListItem::factory()->create(['list_id' => $list->id]);

        Livewire::actingAs($list->user)
            ->test(VoteRound::class, ['list' => $list])
            ->call('vote', $outsider->id)
            ->assertHasErrors(['chosen_item_id']);
    }

    #[Test]
    public function voting_on_a_closed_list_redirects_away()
    {
        [$list] = $this->makeList(3, ['voting_closed_at' => now()]);

        Livewire::actingAs($list->user)
            ->test(VoteRound::class, ['list' => $list])
            ->assertRedirect(route('lists.results', ['list' => $list]));
    }
}
