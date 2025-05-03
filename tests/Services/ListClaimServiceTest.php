<?php

namespace Tests\Services;

use App\Jobs\DeleteUnclaimedList;
use App\Models\DecisionList;
use App\Models\User;
use App\Services\ListClaimService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ListClaimServiceTest extends TestCase
{
    use RefreshDatabase;

    private ListClaimService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ListClaimService;
    }

    /**
     * Test scheduling a list for deletion.
     */
    public function test_schedules_list_for_deletion(): void
    {
        Queue::fake();

        $list = DecisionList::factory()->create([
            'is_anonymous' => true,
        ]);

        $this->service->scheduleForDeletion($list);

        Queue::assertPushedOn('deletions', DeleteUnclaimedList::class);
        Queue::assertPushed(function (DeleteUnclaimedList $job) use ($list) {
            return $job->list->id === $list->id;
        });
    }

    /**
     * Test that non-anonymous lists are not scheduled for deletion.
     */
    public function test_does_not_schedule_non_anonymous_list(): void
    {
        Queue::fake();

        $list = DecisionList::factory()->create([
            'is_anonymous' => false,
        ]);

        $this->service->scheduleForDeletion($list);

        Queue::assertNotPushed(DeleteUnclaimedList::class);
    }

    /**
     * Test claiming a list successfully.
     */
    public function test_claims_list_successfully(): void
    {
        $list = DecisionList::factory()->create([
            'is_anonymous' => true,
            'claimed_at' => null,
        ]);

        $user = User::factory()->create();

        $claimedList = $this->service->claimList($list, $user);

        $this->assertNotNull($claimedList->claimed_at);
        $this->assertEquals($user->id, $claimedList->user_id);
        $this->assertFalse($claimedList->is_anonymous);

        $this->assertDatabaseHas('decision_lists', [
            'id' => $list->id,
            'user_id' => $user->id,
            'is_anonymous' => false,
        ]);
    }

    /**
     * Test that claiming a non-anonymous list throws an exception.
     */
    public function test_cannot_claim_non_anonymous_list(): void
    {
        $list = DecisionList::factory()->create([
            'is_anonymous' => false,
        ]);

        $user = User::factory()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot claim a non-anonymous list');

        $this->service->claimList($list, $user);
    }

    /**
     * Test that claiming an already claimed list throws an exception.
     */
    public function test_cannot_claim_already_claimed_list(): void
    {
        $list = DecisionList::factory()->create([
            'is_anonymous' => true,
            'claimed_at' => now(),
        ]);

        $user = User::factory()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('List is already claimed');

        $this->service->claimList($list, $user);
    }

    /**
     * Test that the deletion job is scheduled with the correct delay.
     */
    public function test_schedules_deletion_with_correct_delay(): void
    {
        Queue::fake();

        $list = DecisionList::factory()->create([
            'is_anonymous' => true,
        ]);

        $this->service->scheduleForDeletion($list);

        Queue::assertPushed(DeleteUnclaimedList::class, function ($job) {
            return $job->delay !== null;
        });
    }
}
