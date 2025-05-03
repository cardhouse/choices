<?php

namespace App\Services;

use App\Models\DecisionList;
use App\Models\ShareCode;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Service class responsible for generating and managing share codes for decision lists.
 * 
 * This service handles:
 * - Generating unique share codes
 * - Managing code expiration
 * - Ensuring one active code per list
 */
class ShareListService
{
    /**
     * Generate a unique share code for a decision list.
     * 
     * @param DecisionList $list The decision list to generate a code for
     * @param \DateTime|null $expiresAt Optional expiration date for the code
     * @return ShareCode The created share code
     * 
     * @throws \RuntimeException If unable to generate a unique code after multiple attempts
     */
    public function generateCode(DecisionList $list, ?\DateTime $expiresAt = null): ShareCode
    {
        try {
            // Deactivate any existing active codes for this list
            $this->deactivateExistingCodes($list);

            // Generate a unique code
            $code = $this->generateUniqueCode();

            // Create the share code
            $shareCode = ShareCode::create([
                'list_id' => $list->id,
                'code' => $code,
                'expires_at' => $expiresAt ? Carbon::instance($expiresAt)->setMicroseconds(0) : null,
            ]);

            Log::info('Share code generated', [
                'list_id' => $list->id,
                'code' => $code,
                'expires_at' => $expiresAt,
            ]);

            return $shareCode;
        } catch (\Exception $e) {
            Log::error('Failed to generate share code', [
                'list_id' => $list->id,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Failed to generate share code: ' . $e->getMessage());
        }
    }

    /**
     * Generate a unique code that doesn't exist in the database.
     * 
     * @return string The generated code
     * 
     * @throws \RuntimeException If unable to generate a unique code after multiple attempts
     */
    private function generateUniqueCode(): string
    {
        $maxAttempts = 10;
        $attempts = 0;

        do {
            if ($attempts >= $maxAttempts) {
                throw new \RuntimeException('Failed to generate a unique code after ' . $maxAttempts . ' attempts');
            }

            // Generate an 8-character code using a custom alphabet
            $code = $this->generateCodeFromAlphabet(8);
            $attempts++;
        } while (ShareCode::where('code', $code)->exists());

        return $code;
    }

    /**
     * Generate a code using a custom alphabet that excludes confusing characters.
     * 
     * @param int $length The length of the code to generate
     * @return string The generated code
     */
    private function generateCodeFromAlphabet(int $length): string
    {
        // Custom alphabet excluding confusing characters (O/0, I/1)
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $code;
    }

    /**
     * Deactivate any existing active codes for a list.
     * 
     * @param DecisionList $list The list to deactivate codes for
     * @return void
     */
    private function deactivateExistingCodes(DecisionList $list): void
    {
        ShareCode::where('list_id', $list->id)
            ->whereNull('deactivated_at')
            ->update(['deactivated_at' => now()]);
    }
} 