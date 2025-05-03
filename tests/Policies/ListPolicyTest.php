<?php

namespace Tests\Policies;

use App\Models\DecisionList;
use App\Models\ShareCode;
use App\Models\User;
use App\Policies\ListPolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ListPolicy $policy;

    private User $user;

    private DecisionList $list;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ListPolicy;
        $this->user = User::factory()->create();
        $this->list = DecisionList::factory()->create();
    }

    /**
     * Test that a user can view their own list.
     */
    public function test_user_can_view_own_list(): void
    {
        $this->list->user_id = $this->user->id;
        $this->list->save();

        $this->assertTrue($this->policy->view($this->user, $this->list));
    }

    /**
     * Test that a user can view an anonymous list.
     */
    public function test_user_can_view_anonymous_list(): void
    {
        $this->list->is_anonymous = true;
        $this->list->claimed_at = null;
        $this->list->user_id = User::factory()->create()->id;
        $this->list->save();

        $this->assertTrue($this->policy->view($this->user, $this->list));
    }

    /**
     * Test that a user can view a list with an active share code.
     */
    public function test_user_can_view_list_with_active_share_code(): void
    {
        ShareCode::factory()->create([
            'list_id' => $this->list->id,
            'expires_at' => Carbon::now()->addDay(),
        ]);

        $this->assertTrue($this->policy->view($this->user, $this->list));
    }

    /**
     * Test that a user cannot view a list they don't own without a share code.
     */
    public function test_user_cannot_view_others_list_without_share_code(): void
    {
        $this->list->is_anonymous = false;
        $this->list->claimed_at = null;
        $this->list->user_id = User::factory()->create()->id;
        $this->list->save();

        $this->assertFalse($this->policy->view($this->user, $this->list));
    }

    /**
     * Test that any authenticated user can create lists.
     */
    public function test_any_user_can_create_lists(): void
    {
        $this->assertTrue($this->policy->create($this->user));
    }

    /**
     * Test that a user can update their own list.
     */
    public function test_user_can_update_own_list(): void
    {
        $this->list->user_id = $this->user->id;
        $this->list->save();

        $this->assertTrue($this->policy->update($this->user, $this->list));
    }

    /**
     * Test that a user cannot update another user's list.
     */
    public function test_user_cannot_update_others_list(): void
    {
        $this->assertFalse($this->policy->update($this->user, $this->list));
    }

    /**
     * Test that a user can delete their own list.
     */
    public function test_user_can_delete_own_list(): void
    {
        $this->list->user_id = $this->user->id;
        $this->list->save();

        $this->assertTrue($this->policy->delete($this->user, $this->list));
    }

    /**
     * Test that a user cannot delete another user's list.
     */
    public function test_user_cannot_delete_others_list(): void
    {
        $this->assertFalse($this->policy->delete($this->user, $this->list));
    }

    /**
     * Test that a user can claim an anonymous, unclaimed list.
     */
    public function test_user_can_claim_anonymous_unclaimed_list(): void
    {
        $this->list->is_anonymous = true;
        $this->list->claimed_at = null;
        $this->list->save();

        $this->assertTrue($this->policy->claim($this->user, $this->list));
    }

    /**
     * Test that a user cannot claim a non-anonymous list.
     */
    public function test_user_cannot_claim_non_anonymous_list(): void
    {
        $this->list->is_anonymous = false;
        $this->list->claimed_at = null;
        $this->list->save();

        $this->assertFalse($this->policy->claim($this->user, $this->list));
    }

    /**
     * Test that a user cannot claim an already claimed list.
     */
    public function test_user_cannot_claim_already_claimed_list(): void
    {
        $this->list->is_anonymous = true;
        $this->list->claimed_at = now();
        $this->list->save();

        $this->assertFalse($this->policy->claim($this->user, $this->list));
    }

    public function test_owner_can_view_results()
    {
        $user = User::factory()->create();
        $list = DecisionList::factory()->create([
            'user_id' => $user->id,
            'voting_completed_at' => now(),
        ]);

        $this->assertTrue($this->policy->viewResults($user, $list));
    }

    public function test_anonymous_list_can_view_results_after_voting_complete()
    {
        $user = User::factory()->create();
        $list = DecisionList::factory()->create([
            'user_id' => null,
            'is_anonymous' => true,
            'voting_completed_at' => now(),
        ]);

        $this->assertTrue($this->policy->viewResults($user, $list));
    }

    public function test_anonymous_list_cannot_view_results_before_voting_complete()
    {
        $user = User::factory()->create();
        $list = DecisionList::factory()->create([
            'user_id' => null,
            'is_anonymous' => true,
            'voting_completed_at' => null,
        ]);

        $this->assertFalse($this->policy->viewResults($user, $list));
    }

    public function test_shared_list_can_view_results_after_voting_complete()
    {
        $user = User::factory()->create();
        $list = DecisionList::factory()->create([
            'voting_completed_at' => now(),
        ]);

        ShareCode::factory()->create([
            'list_id' => $list->id,
            'expires_at' => now()->addDay(),
        ]);

        $this->assertTrue($this->policy->viewResults($user, $list));
    }

    public function test_shared_list_cannot_view_results_before_voting_complete()
    {
        $user = User::factory()->create();
        $list = DecisionList::factory()->create([
            'voting_completed_at' => null,
        ]);

        ShareCode::factory()->create([
            'list_id' => $list->id,
            'expires_at' => now()->addDay(),
        ]);

        $this->assertFalse($this->policy->viewResults($user, $list));
    }
}
