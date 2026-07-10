<?php

namespace Tests\Policies;

use App\Models\DecisionList;
use App\Models\ListParticipant;
use App\Models\User;
use App\Policies\ListPolicy;
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

    private function makeParticipant(User $user, DecisionList $list): void
    {
        ListParticipant::factory()->create([
            'list_id' => $list->id,
            'user_id' => $user->id,
        ]);
    }

    // ── view ────────────────────────────────────────────────────────────

    public function test_owner_can_view_own_list(): void
    {
        $this->list->update(['user_id' => $this->user->id]);

        $this->assertTrue($this->policy->view($this->user, $this->list));
    }

    public function test_anyone_can_view_unclaimed_anonymous_list(): void
    {
        $list = DecisionList::factory()->anonymous()->create();

        $this->assertTrue($this->policy->view($this->user, $list));
        $this->assertTrue($this->policy->view(null, $list));
    }

    public function test_participant_can_view_list(): void
    {
        $this->makeParticipant($this->user, $this->list);

        $this->assertTrue($this->policy->view($this->user, $this->list));
    }

    public function test_stranger_cannot_view_others_list(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->list));
        $this->assertFalse($this->policy->view(null, $this->list));
    }

    // ── create / update / delete / claim ────────────────────────────────

    public function test_any_user_can_create_lists(): void
    {
        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_only_owner_can_update(): void
    {
        $this->assertFalse($this->policy->update($this->user, $this->list));

        $this->list->update(['user_id' => $this->user->id]);
        $this->assertTrue($this->policy->update($this->user, $this->list->fresh()));
    }

    public function test_only_owner_can_delete(): void
    {
        $this->assertFalse($this->policy->delete($this->user, $this->list));

        $this->list->update(['user_id' => $this->user->id]);
        $this->assertTrue($this->policy->delete($this->user, $this->list->fresh()));
    }

    public function test_user_can_claim_anonymous_unclaimed_list(): void
    {
        $list = DecisionList::factory()->anonymous()->create();

        $this->assertTrue($this->policy->claim($this->user, $list));
    }

    public function test_user_cannot_claim_non_anonymous_list(): void
    {
        $this->assertFalse($this->policy->claim($this->user, $this->list));
    }

    public function test_user_cannot_claim_already_claimed_list(): void
    {
        $list = DecisionList::factory()->anonymous()->claimed()->create();

        $this->assertFalse($this->policy->claim($this->user, $list));
    }

    // ── vote ────────────────────────────────────────────────────────────

    public function test_owner_can_vote_while_open(): void
    {
        $this->list->update(['user_id' => $this->user->id]);

        $this->assertTrue($this->policy->vote($this->user, $this->list));
    }

    public function test_participant_can_vote_while_open(): void
    {
        $this->makeParticipant($this->user, $this->list);

        $this->assertTrue($this->policy->vote($this->user, $this->list));
    }

    public function test_guest_can_vote_on_unclaimed_anonymous_list(): void
    {
        $list = DecisionList::factory()->anonymous()->create();

        $this->assertTrue($this->policy->vote(null, $list));
    }

    public function test_stranger_cannot_vote(): void
    {
        $this->assertFalse($this->policy->vote($this->user, $this->list));
        $this->assertFalse($this->policy->vote(null, $this->list));
    }

    public function test_no_one_can_vote_once_closed(): void
    {
        $this->list->update([
            'user_id' => $this->user->id,
            'voting_closed_at' => now(),
        ]);

        $this->assertFalse($this->policy->vote($this->user, $this->list->fresh()));
    }

    public function test_voting_closes_when_deadline_passes(): void
    {
        $this->list->update([
            'user_id' => $this->user->id,
            'voting_closes_at' => now()->subMinute(),
        ]);

        $this->assertFalse($this->policy->vote($this->user, $this->list->fresh()));
    }

    // ── viewResults ─────────────────────────────────────────────────────

    public function test_owner_can_view_results_even_while_open(): void
    {
        $this->list->update(['user_id' => $this->user->id]);

        $this->assertTrue($this->policy->viewResults($this->user, $this->list));
    }

    public function test_participant_can_view_results_after_close(): void
    {
        $this->makeParticipant($this->user, $this->list);
        $this->list->update(['voting_closed_at' => now()]);

        $this->assertTrue($this->policy->viewResults($this->user, $this->list->fresh()));
    }

    public function test_participant_cannot_view_results_while_open(): void
    {
        $this->makeParticipant($this->user, $this->list);

        $this->assertFalse($this->policy->viewResults($this->user, $this->list));
    }

    public function test_stranger_cannot_view_results_after_close(): void
    {
        $this->list->update(['voting_closed_at' => now()]);

        $this->assertFalse($this->policy->viewResults($this->user, $this->list->fresh()));
    }

    // ── manageVoting ────────────────────────────────────────────────────

    public function test_only_owner_can_manage_voting(): void
    {
        $this->assertFalse($this->policy->manageVoting($this->user, $this->list));

        $this->makeParticipant($this->user, $this->list);
        $this->assertFalse($this->policy->manageVoting($this->user, $this->list));

        $this->list->update(['user_id' => $this->user->id]);
        $this->assertTrue($this->policy->manageVoting($this->user, $this->list->fresh()));
    }
}
