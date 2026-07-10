<?php

namespace Tests\Actions;

use App\Actions\Voting\CloseVoting;
use App\Models\DecisionList;
use App\Models\ShareCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloseVotingTest extends TestCase
{
    use RefreshDatabase;

    public function test_closes_voting_and_deactivates_share_codes(): void
    {
        $list = DecisionList::factory()->create();
        $code = ShareCode::factory()->create(['list_id' => $list->id, 'expires_at' => null]);

        (new CloseVoting)->handle($list);

        $this->assertNotNull($list->fresh()->voting_closed_at);
        $this->assertNotNull($code->fresh()->deactivated_at);
        $this->assertTrue($list->fresh()->isVotingClosed());
    }

    public function test_closing_an_already_closed_list_is_a_no_op(): void
    {
        $closedAt = now()->subDay();
        $list = DecisionList::factory()->create(['voting_closed_at' => $closedAt]);

        (new CloseVoting)->handle($list);

        $this->assertEquals(
            $closedAt->toDateTimeString(),
            $list->fresh()->voting_closed_at->toDateTimeString()
        );
    }
}
