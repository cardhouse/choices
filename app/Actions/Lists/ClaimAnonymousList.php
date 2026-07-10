<?php

namespace App\Actions\Lists;

use App\Models\DecisionList;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Transfers an anonymous list (and the session's votes on it) to a
 * registered user, which stops the pending deletion job from acting.
 */
class ClaimAnonymousList
{
    /**
     * @throws \RuntimeException If the list is already claimed or not anonymous
     */
    public function handle(DecisionList $list, User $user, ?string $sessionToken = null): DecisionList
    {
        if (! $list->is_anonymous) {
            throw new \RuntimeException('Cannot claim a non-anonymous list');
        }

        if ($list->claimed_at !== null) {
            throw new \RuntimeException('List is already claimed');
        }

        return DB::transaction(function () use ($list, $user, $sessionToken) {
            $list->user_id = $user->id;
            $list->is_anonymous = false;
            $list->claimed_at = now();
            $list->save();

            if ($sessionToken !== null) {
                Vote::whereIn('matchup_id', $list->matchups()->pluck('id'))
                    ->whereNull('user_id')
                    ->where('session_token', $sessionToken)
                    ->update(['user_id' => $user->id, 'session_token' => null]);
            }

            Log::info('List claimed by user', [
                'list_id' => $list->id,
                'user_id' => $user->id,
            ]);

            return $list;
        });
    }
}
