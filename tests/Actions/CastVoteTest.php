<?php

namespace Tests\Actions;

use App\Actions\Voting\CastVote;
use App\Exceptions\InvalidVoteException;
use App\Exceptions\VotingClosedException;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\User;
use App\Support\Voter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CastVoteTest extends TestCase
{
    use RefreshDatabase;

    private DecisionList $list;

    private Matchup $matchup;

    /** @var \Illuminate\Support\Collection<int, DecisionListItem> */
    private $items;

    protected function setUp(): void
    {
        parent::setUp();

        $this->list = DecisionList::factory()->create();
        $this->items = DecisionListItem::factory()->count(3)->create(['list_id' => $this->list->id]);
        $this->matchup = Matchup::factory()->create([
            'list_id' => $this->list->id,
            'item_a_id' => $this->items[0]->id,
            'item_b_id' => $this->items[1]->id,
        ]);
    }

    public function test_records_a_vote_for_a_user(): void
    {
        $user = User::factory()->create();

        $vote = (new CastVote)->handle(Voter::user($user), $this->matchup, $this->items[0]);

        $this->assertDatabaseHas('votes', [
            'id' => $vote->id,
            'matchup_id' => $this->matchup->id,
            'user_id' => $user->id,
            'chosen_item_id' => $this->items[0]->id,
        ]);
    }

    public function test_records_a_vote_for_an_anonymous_session(): void
    {
        $vote = (new CastVote)->handle(Voter::session('guest-token'), $this->matchup, $this->items[1]);

        $this->assertNull($vote->user_id);
        $this->assertEquals('guest-token', $vote->session_token);
    }

    public function test_changing_a_vote_updates_instead_of_duplicating(): void
    {
        $user = User::factory()->create();
        $voter = Voter::user($user);

        (new CastVote)->handle($voter, $this->matchup, $this->items[0]);
        (new CastVote)->handle($voter, $this->matchup, $this->items[1]);

        $this->assertEquals(1, $this->matchup->votes()->count());
        $this->assertEquals($this->items[1]->id, $this->matchup->votes()->first()->chosen_item_id);
    }

    public function test_two_voters_can_vote_on_the_same_matchup(): void
    {
        [$alice, $bob] = User::factory()->count(2)->create();

        (new CastVote)->handle(Voter::user($alice), $this->matchup, $this->items[0]);
        (new CastVote)->handle(Voter::user($bob), $this->matchup, $this->items[1]);

        $this->assertEquals(2, $this->matchup->votes()->count());
    }

    public function test_rejects_item_not_in_matchup(): void
    {
        $this->expectException(InvalidVoteException::class);

        (new CastVote)->handle(Voter::session('t'), $this->matchup, $this->items[2]);
    }

    public function test_rejects_vote_when_voting_is_closed(): void
    {
        $this->list->update(['voting_closed_at' => now()]);

        $this->expectException(VotingClosedException::class);

        (new CastVote)->handle(Voter::session('t'), $this->matchup, $this->items[0]);
    }

    public function test_rejects_vote_when_deadline_has_passed(): void
    {
        $this->list->update(['voting_closes_at' => now()->subMinute()]);

        $this->expectException(VotingClosedException::class);

        (new CastVote)->handle(Voter::session('t'), $this->matchup, $this->items[0]);
    }
}
