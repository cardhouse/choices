<?php

namespace Tests\Actions;

use App\Actions\Sharing\RedeemShareCode;
use App\Exceptions\InvalidShareCodeException;
use App\Models\DecisionList;
use App\Models\ShareCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedeemShareCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_redeeming_makes_the_user_a_participant(): void
    {
        $list = DecisionList::factory()->create();
        $code = ShareCode::factory()->create(['list_id' => $list->id]);
        $friend = User::factory()->create();

        $joined = (new RedeemShareCode)->handle($code->code, $friend);

        $this->assertEquals($list->id, $joined->id);
        $this->assertTrue($list->hasParticipant($friend));
        $this->assertDatabaseHas('list_participants', [
            'list_id' => $list->id,
            'user_id' => $friend->id,
            'share_code_id' => $code->id,
        ]);
    }

    public function test_redeeming_is_idempotent(): void
    {
        $list = DecisionList::factory()->create();
        $code = ShareCode::factory()->create(['list_id' => $list->id]);
        $friend = User::factory()->create();

        (new RedeemShareCode)->handle($code->code, $friend);
        (new RedeemShareCode)->handle($code->code, $friend);

        $this->assertEquals(1, $list->participants()->count());
    }

    public function test_code_is_case_insensitive(): void
    {
        $list = DecisionList::factory()->create();
        $code = ShareCode::factory()->create(['list_id' => $list->id, 'code' => 'ABCD2345']);
        $friend = User::factory()->create();

        $joined = (new RedeemShareCode)->handle('abcd2345', $friend);

        $this->assertEquals($list->id, $joined->id);
    }

    public function test_owner_redeeming_does_not_become_participant(): void
    {
        $list = DecisionList::factory()->create();
        $code = ShareCode::factory()->create(['list_id' => $list->id]);

        (new RedeemShareCode)->handle($code->code, $list->user);

        $this->assertEquals(0, $list->participants()->count());
    }

    public function test_rejects_unknown_code(): void
    {
        $this->expectException(InvalidShareCodeException::class);

        (new RedeemShareCode)->handle('ZZZZZZZZ', User::factory()->create());
    }

    public function test_rejects_expired_code(): void
    {
        $code = ShareCode::factory()->expired()->create();

        $this->expectException(InvalidShareCodeException::class);

        (new RedeemShareCode)->handle($code->code, User::factory()->create());
    }

    public function test_rejects_deactivated_code(): void
    {
        $code = ShareCode::factory()->deactivated()->create();

        $this->expectException(InvalidShareCodeException::class);

        (new RedeemShareCode)->handle($code->code, User::factory()->create());
    }
}
