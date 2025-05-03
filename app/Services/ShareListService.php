<?php

namespace App\Services;

use App\Exceptions\ShareCodeGenerationException;
use App\Models\DecisionList;
use App\Models\ShareCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
     * Custom alphabet for share codes, excluding confusing characters (O/0, I/1)
     * and using only uppercase letters for better readability.
     */
    private const CUSTOM_ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    /**
     * Length of the generated share code.
     */
    private const CODE_LENGTH = 8;

    /**
     * Maximum number of attempts to generate a unique code before failing.
     */
    private const MAX_GENERATION_ATTEMPTS = 5;

    /**
     * Generates a unique share code for a decision list.
     *
     * This method creates a new share code for the given list with an optional expiration date.
     * It uses a custom alphabet to avoid confusing characters and ensures uniqueness through
     * multiple generation attempts if necessary.
     *
     * @param  DecisionList  $list  The decision list to generate a share code for
     * @param  Carbon|null  $expiresAt  Optional expiration date for the share code
     * @return ShareCode The newly created share code
     *
     * @throws ShareCodeGenerationException If unable to generate a unique code after multiple attempts
     */
    public function generateCode(DecisionList $list, ?Carbon $expiresAt = null): ShareCode
    {
        return DB::transaction(function () use ($list, $expiresAt) {
            // Deactivate any existing active codes for this list
            $this->deactivateExistingCodes($list);

            // Generate a new unique code
            $code = $this->generateUniqueCode();

            // Create and return the new share code
            return ShareCode::create([
                'list_id' => $list->id,
                'code' => $code,
                'expires_at' => $expiresAt,
            ]);
        });
    }

    /**
     * Deactivates any existing active share codes for the given list.
     *
     * @param  DecisionList  $list  The decision list to deactivate codes for
     */
    private function deactivateExistingCodes(DecisionList $list): void
    {
        ShareCode::where('list_id', $list->id)
            ->whereNull('deactivated_at')
            ->update(['deactivated_at' => now()]);
    }

    /**
     * Generates a unique share code using the custom alphabet.
     *
     * @return string The generated code
     *
     * @throws ShareCodeGenerationException If unable to generate a unique code
     */
    private function generateUniqueCode(): string
    {
        $attempts = 0;

        do {
            $code = $this->generateRandomCode();
            $attempts++;

            if ($attempts > self::MAX_GENERATION_ATTEMPTS) {
                Log::error('Failed to generate unique share code after multiple attempts');
                throw new ShareCodeGenerationException(
                    'Unable to generate a unique share code after '.self::MAX_GENERATION_ATTEMPTS.' attempts'
                );
            }
        } while (ShareCode::where('code', $code)->exists());

        return $code;
    }

    /**
     * Generates a random code using the custom alphabet.
     *
     * @return string The generated code
     */
    protected function generateRandomCode(): string
    {
        $code = '';
        $alphabetLength = strlen(self::CUSTOM_ALPHABET);

        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= self::CUSTOM_ALPHABET[random_int(0, $alphabetLength - 1)];
        }

        return $code;
    }
}
