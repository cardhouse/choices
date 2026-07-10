<?php

namespace Tests\Actions;

use App\Actions\Lists\ScheduleListDeletion;
use App\Jobs\DeleteUnclaimedList;
use App\Models\DecisionList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ScheduleListDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedules_anonymous_list_for_deletion(): void
    {
        Queue::fake();
        $list = DecisionList::factory()->anonymous()->create();

        (new ScheduleListDeletion)->handle($list);

        Queue::assertPushedOn('deletions', DeleteUnclaimedList::class);
        Queue::assertPushed(function (DeleteUnclaimedList $job) use ($list) {
            return $job->list->id === $list->id
                && $job->delay?->diffInMinutes(now(), true) >= ScheduleListDeletion::DELETION_DELAY_MINUTES - 1;
        });
    }

    public function test_does_not_schedule_non_anonymous_list(): void
    {
        Queue::fake();
        $list = DecisionList::factory()->create(['is_anonymous' => false]);

        (new ScheduleListDeletion)->handle($list);

        Queue::assertNotPushed(DeleteUnclaimedList::class);
    }
}
