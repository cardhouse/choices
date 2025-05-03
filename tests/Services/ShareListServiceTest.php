<?php

namespace Tests\Services;

use App\Models\DecisionList;
use App\Models\ShareCode;
use App\Services\ShareListService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShareListServiceTest extends TestCase
{
    use RefreshDatabase;

    private ShareListService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ShareListService();
    }

    /**
     * Test generating a share code without expiration
     */
    public function test_generates_share_code_without_expiration(): void
    {
        $list = DecisionList::factory()->create();

        $shareCode = $this->service->generateCode($list);

        $this->assertInstanceOf(ShareCode::class, $shareCode);
        $this->assertEquals($list->id, $shareCode->list_id);
        $this->assertNull($shareCode->expires_at);
        $this->assertNull($shareCode->deactivated_at);
        $this->assertEquals(8, strlen($shareCode->code));
    }

    /**
     * Test generating a share code with expiration
     */
    public function test_generates_share_code_with_expiration(): void
    {
        $list = DecisionList::factory()->create();
        $expiresAt = Carbon::now()->addDays(7)->setMicroseconds(0);

        $shareCode = $this->service->generateCode($list, $expiresAt);

        $this->assertInstanceOf(ShareCode::class, $shareCode);
        $this->assertEquals($list->id, $shareCode->list_id);
        $this->assertEquals($expiresAt->toDateTimeString(), $shareCode->expires_at->toDateTimeString());
        $this->assertNull($shareCode->deactivated_at);
    }

    /**
     * Test deactivating existing codes when generating a new one
     */
    public function test_deactivates_existing_codes(): void
    {
        $list = DecisionList::factory()->create();

        // Create an active share code
        $existingCode = ShareCode::factory()->create([
            'list_id' => $list->id,
            'deactivated_at' => null,
        ]);

        // Generate a new code
        $newCode = $this->service->generateCode($list);

        // Refresh the existing code from database
        $existingCode->refresh();

        $this->assertNotNull($existingCode->deactivated_at);
        $this->assertNull($newCode->deactivated_at);
    }

    /**
     * Test generating unique codes
     */
    public function test_generates_unique_codes(): void
    {
        $list = DecisionList::factory()->create();

        // Generate multiple codes
        $codes = collect();
        for ($i = 0; $i < 5; $i++) {
            $codes->push($this->service->generateCode($list));
        }

        // Verify all codes are unique
        $uniqueCodes = $codes->pluck('code')->unique();
        $this->assertEquals($codes->count(), $uniqueCodes->count());
    }

    /**
     * Test code generation with custom alphabet
     */
    public function test_generates_codes_with_custom_alphabet(): void
    {
        $list = DecisionList::factory()->create();
        $shareCode = $this->service->generateCode($list);

        // Verify code only contains allowed characters
        $this->assertMatchesRegularExpression('/^[23456789ABCDEFGHJKLMNPQRSTUVWXYZ]+$/', $shareCode->code);
        $this->assertDoesNotMatchRegularExpression('/[O0I1]/', $shareCode->code);
    }

    /**
     * Test handling of code generation failure
     */
    public function test_handles_code_generation_failure(): void
    {
        $list = DecisionList::factory()->create();

        // Create a mock service that always fails to generate a code
        $mockService = $this->partialMock(ShareListService::class, function ($mock) {
            $mock->shouldReceive('generateCode')
                ->once()
                ->andThrow(new \RuntimeException('Failed to generate a unique code after 10 attempts'));
        });

        // Expect an exception when trying to generate a code
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to generate a unique code after 10 attempts');

        $mockService->generateCode($list);
    }
} 