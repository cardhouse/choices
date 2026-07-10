<?php

namespace Tests\Actions;

use App\Actions\Lists\ClaimAnonymousList;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimAnonymousListTest extends TestCase
{
    use RefreshDatabase;

    public function test_claims_list_and_reassigns_session_votes(): void
    {
        $user = User::factory()->create();
        $list = DecisionList::factory()->anonymous()->create();
        $items = DecisionListItem::factory()->count(2)->create(['list_id' => $list->id]);
        $matchup = Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $items[0]->id,
            'item_b_id' => $items[1]->id,
        ]);
        $vote = Vote::factory()->create([
            'matchup_id' => $matchup->id,
            'chosen_item_id' => $items[0]->id,
            'user_id' => null,
            'session_token' => 'guest-session',
        ]);

        $claimed = (new ClaimAnonymousList)->handle($list, $user, 'guest-session');

        $this->assertEquals($user->id, $claimed->user_id);
        $this->assertFalse($claimed->is_anonymous);
        $this->assertNotNull($claimed->claimed_at);

        $vote->refresh();
        $this->assertEquals($user->id, $vote->user_id);
        $this->assertNull($vote->session_token);
    }

    public function test_cannot_claim_non_anonymous_list(): void
    {
        $list = DecisionList::factory()->create();
        $user = User::factory()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot claim a non-anonymous list');

        (new ClaimAnonymousList)->handle($list, $user);
    }

    public function test_cannot_claim_already_claimed_list(): void
    {
        $list = DecisionList::factory()->anonymous()->claimed()->create();
        $user = User::factory()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('List is already claimed');

        (new ClaimAnonymousList)->handle($list, $user);
    }
}
