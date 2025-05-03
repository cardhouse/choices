<?php

namespace Tests\Services;

use App\Exceptions\ShareCodeGenerationException;
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
     * Test that a new share code is generated successfully.
     */
    public function test_generates_new_share_code(): void
    {
        $list = DecisionList::factory()->create();
        $expiresAt = Carbon::now()->addDays(7)->setMicroseconds(0);

        $shareCode = $this->service->generateCode($list, $expiresAt);

        $this->assertInstanceOf(ShareCode::class, $shareCode);
        $this->assertEquals($list->id, $shareCode->list_id);
        $this->assertEquals($expiresAt->toDateTimeString(), $shareCode->expires_at->toDateTimeString());
        $this->assertNull($shareCode->deactivated_at);
        $this->assertMatchesRegularExpression('/^[2-9A-HJ-NP-Z]{8}$/', $shareCode->code);
    }

    /**
     * Test that existing active codes are deactivated when generating a new one.
     */
    public function test_deactivates_existing_codes(): void
    {
        $list = DecisionList::factory()->create();
        $existingCode = ShareCode::factory()->create([
            'list_id' => $list->id,
            'deactivated_at' => null,
        ]);

        $newCode = $this->service->generateCode($list);

        $this->assertNotNull($existingCode->fresh()->deactivated_at);
        $this->assertNotEquals($existingCode->code, $newCode->code);
    }

    /**
     * Test that generated codes are unique.
     */
    public function test_generates_unique_codes(): void
    {
        $list = DecisionList::factory()->create();
        $codes = [];

        // Generate multiple codes and verify uniqueness
        for ($i = 0; $i < 5; $i++) {
            $code = $this->service->generateCode($list);
            $this->assertNotContains($code->code, $codes);
            $codes[] = $code->code;
        }
    }

    /**
     * Test that the service throws an exception when unable to generate a unique code.
     */
    public function test_throws_exception_when_unable_to_generate_unique_code(): void
    {
        $list = DecisionList::factory()->create();
        
        // Create a code that will cause a collision
        ShareCode::factory()->create([
            'code' => 'TESTCODE1',
        ]);

        // Create a mock that always returns the same code
        $service = $this->getMockBuilder(ShareListService::class)
            ->onlyMethods(['generateRandomCode'])
            ->getMock();
        
        $service->expects($this->atLeast(5))
            ->method('generateRandomCode')
            ->willReturn('TESTCODE1');

        $this->expectException(ShareCodeGenerationException::class);
        $service->generateCode($list);
    }

    /**
     * Test that a share code can be generated without an expiration date.
     */
    public function test_generates_code_without_expiration(): void
    {
        $list = DecisionList::factory()->create();

        $shareCode = $this->service->generateCode($list);

        $this->assertNull($shareCode->expires_at);
    }

    /**
     * Test that the generated code uses the correct alphabet.
     */
    public function test_generated_code_uses_correct_alphabet(): void
    {
        $list = DecisionList::factory()->create();
        $shareCode = $this->service->generateCode($list);

        // Verify the code only contains characters from our custom alphabet
        $this->assertMatchesRegularExpression('/^[2-9A-HJ-NP-Z]{8}$/', $shareCode->code);
        $this->assertDoesNotMatchRegularExpression('/[01OIl]/', $shareCode->code);
    }
} 