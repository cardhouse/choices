<?php

namespace Tests\Actions;

use App\Actions\Sharing\RevokeShareCode;
use App\Models\DecisionList;
use App\Models\ShareCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevokeShareCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_revokes_active_codes_but_keeps_voting_open(): void
    {
        $list = DecisionList::factory()->create();
        $code = ShareCode::factory()->create(['list_id' => $list->id]);

        (new RevokeShareCode)->handle($list);

        $this->assertNotNull($code->fresh()->deactivated_at);
        $this->assertFalse($list->fresh()->isShared());
        $this->assertTrue($list->fresh()->isVotingOpen());
    }
}
