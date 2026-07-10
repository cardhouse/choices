<?php

namespace App\Actions\Sharing;

use App\Exceptions\ShareCodeGenerationException;
use App\Exceptions\VotingClosedException;
use App\Models\DecisionList;
use App\Models\ShareCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Mints a unique share code for a list, replacing any active one. An optional
 * expiry doubles as the list's voting deadline.
 */
class GenerateShareCode
{
    /**
     * Custom alphabet excluding confusing characters (O/0, I/1),
     * uppercase only for readability.
     */
    private const CUSTOM_ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    private const CODE_LENGTH = 8;

    private const MAX_GENERATION_ATTEMPTS = 5;

    /**
     * @throws VotingClosedException If voting has already closed on the list
     * @throws ShareCodeGenerationException If a unique code cannot be generated
     */
    public function handle(DecisionList $list, ?Carbon $expiresAt = null): ShareCode
    {
        if ($list->isVotingClosed()) {
            throw VotingClosedException::forList($list->id);
        }

        return DB::transaction(function () use ($list, $expiresAt) {
            $list->shareCodes()
                ->whereNull('deactivated_at')
                ->update(['deactivated_at' => now()]);

            $list->voting_closes_at = $expiresAt;
            $list->save();

            return ShareCode::create([
                'list_id' => $list->id,
                'code' => $this->generateUniqueCode(),
                'expires_at' => $expiresAt,
            ]);
        });
    }

    /**
     * @throws ShareCodeGenerationException
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
