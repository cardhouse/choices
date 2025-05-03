<?php

namespace Tests\Jobs;

use App\Jobs\DeleteUnclaimedList;
use App\Models\DecisionList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteUnclaimedListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an unclaimed list is deleted.
     */
    public function test_deletes_unclaimed_list(): void
    {
        $list = DecisionList::factory()->create([
            'is_anonymous' => true,
            'claimed_at' => null,
        ]);

        $job = new DeleteUnclaimedList($list);
        $job->handle();

        $this->assertDatabaseMissing('decision_lists', [
            'id' => $list->id,
        ]);
    }

    /**
     * Test that a claimed list is not deleted.
     */
    public function test_does_not_delete_claimed_list(): void
    {
        $list = DecisionList::factory()->create([
            'is_anonymous' => true,
            'claimed_at' => now(),
        ]);

        $job = new DeleteUnclaimedList($list);
        $job->handle();

        $this->assertDatabaseHas('decision_lists', [
            'id' => $list->id,
        ]);
    }

    /**
     * Test that the job has the correct tags.
     */
    public function test_job_has_correct_tags(): void
    {
        $list = DecisionList::factory()->create();
        $job = new DeleteUnclaimedList($list);

        $this->assertEquals(
            ['list:'.$list->id, 'delete_unclaimed'],
            $job->tags()
        );
    }
}
