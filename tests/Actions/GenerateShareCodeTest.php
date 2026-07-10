<?php

namespace Tests\Actions;

use App\Actions\Sharing\GenerateShareCode;
use App\Exceptions\ShareCodeGenerationException;
use App\Exceptions\VotingClosedException;
use App\Models\DecisionList;
use App\Models\ShareCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateShareCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_an_eight_character_code_from_the_safe_alphabet(): void
    {
        $list = DecisionList::factory()->create();

        $code = (new GenerateShareCode)->handle($list);

        $this->assertEquals(8, strlen($code->code));
        $this->assertMatchesRegularExpression('/^[23456789ABCDEFGHJKLMNPQRSTUVWXYZ]{8}$/', $code->code);
        $this->assertNull($code->expires_at);
    }

    public function test_expiry_becomes_the_lists_voting_deadline(): void
    {
        $list = DecisionList::factory()->create();
        $deadline = now()->addDays(3);

        $code = (new GenerateShareCode)->handle($list, $deadline);

        $this->assertEquals($deadline->toDateTimeString(), $code->expires_at->toDateTimeString());
        $this->assertEquals(
            $deadline->toDateTimeString(),
            $list->fresh()->voting_closes_at->toDateTimeString()
        );
    }

    public function test_deactivates_previous_codes(): void
    {
        $list = DecisionList::factory()->create();
        $old = (new GenerateShareCode)->handle($list);

        (new GenerateShareCode)->handle($list);

        $this->assertNotNull($old->fresh()->deactivated_at);
        $this->assertEquals(1, $list->shareCodes()->active()->count());
    }

    public function test_cannot_share_a_closed_list(): void
    {
        $list = DecisionList::factory()->closed()->create();

        $this->expectException(VotingClosedException::class);

        (new GenerateShareCode)->handle($list);
    }

    public function test_throws_when_unable_to_generate_unique_code(): void
    {
        $list = DecisionList::factory()->create();

        $action = new class extends GenerateShareCode
        {
            protected function generateRandomCode(): string
            {
                return 'AAAAAAAA';
            }
        };

        ShareCode::factory()->create(['code' => 'AAAAAAAA']);

        $this->expectException(ShareCodeGenerationException::class);

        $action->handle($list);
    }
}
