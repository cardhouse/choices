<?php

namespace Tests\Policies;

use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use App\Policies\VotePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VotePolicyTest extends TestCase
{
    use RefreshDatabase;

    private VotePolicy $policy;

    private User $user;

    private Vote $vote;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new VotePolicy;
        $this->user = User::factory()->create();

        // Create a list with two items and a matchup
        $list = DecisionList::factory()->create([
            'is_anonymous' => false,
            'user_id' => User::factory()->create()->id, // Create a different user to own the list
        ]);
        $itemA = DecisionListItem::factory()->create(['list_id' => $list->id]);
        $itemB = DecisionListItem::factory()->create(['list_id' => $list->id]);
        $matchup = Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $itemA->id,
            'item_b_id' => $itemB->id,
        ]);

        $this->vote = Vote::factory()->create([
            'matchup_id' => $matchup->id,
            'user_id' => $this->user->id,
            'chosen_item_id' => $itemA->id,
        ]);
    }

    /**
     * Test that a user can view a vote if they can view the parent list.
     */
    public function test_user_can_view_vote_if_can_view_list(): void
    {
        $this->vote->matchup->list->user_id = $this->user->id;
        $this->vote->matchup->list->save();

        $this->assertTrue($this->policy->view($this->user, $this->vote));
    }

    /**
     * Test that a user cannot view a vote if they cannot view the parent list.
     */
    public function test_user_cannot_view_vote_if_cannot_view_list(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->vote));
    }

    /**
     * Test that a user can create a vote if they can view the list and the item belongs to the matchup.
     */
    public function test_user_can_create_vote_with_valid_item(): void
    {
        $this->vote->matchup->list->user_id = $this->user->id;
        $this->vote->matchup->list->save();

        $this->assertTrue($this->policy->create($this->user, $this->vote));
    }

    /**
     * Test that a user cannot create a vote if the item does not belong to the matchup.
     */
    public function test_user_cannot_create_vote_with_invalid_item(): void
    {
        $this->vote->matchup->list->user_id = $this->user->id;
        $this->vote->matchup->list->save();

        // Create a new item that doesn't belong to the matchup
        $invalidItem = DecisionListItem::factory()->create(['list_id' => $this->vote->matchup->list->id]);
        $this->vote->chosen_item_id = $invalidItem->id;

        $this->assertFalse($this->policy->create($this->user, $this->vote));
    }

    /**
     * Test that a user can update their own vote with a valid item.
     */
    public function test_user_can_update_own_vote_with_valid_item(): void
    {
        $this->assertTrue($this->policy->update($this->user, $this->vote));
    }

    /**
     * Test that a user cannot update another user's vote.
     */
    public function test_user_cannot_update_others_vote(): void
    {
        $otherUser = User::factory()->create();
        $this->assertFalse($this->policy->update($otherUser, $this->vote));
    }

    /**
     * Test that a user cannot update a vote with an invalid item.
     */
    public function test_user_cannot_update_vote_with_invalid_item(): void
    {
        // Create a new item that doesn't belong to the matchup
        $invalidItem = DecisionListItem::factory()->create(['list_id' => $this->vote->matchup->list->id]);
        $this->vote->chosen_item_id = $invalidItem->id;

        $this->assertFalse($this->policy->update($this->user, $this->vote));
    }

    /**
     * Test that a user can delete their own vote.
     */
    public function test_user_can_delete_own_vote(): void
    {
        $this->assertTrue($this->policy->delete($this->user, $this->vote));
    }

    /**
     * Test that a user cannot delete another user's vote.
     */
    public function test_user_cannot_delete_others_vote(): void
    {
        $otherUser = User::factory()->create();
        $this->assertFalse($this->policy->delete($otherUser, $this->vote));
    }
}
