<?php

namespace App\Services;

use App\Jobs\DeleteUnclaimedList;
use App\Models\DecisionList;
use App\Models\User;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing list claiming and deletion jobs.
 *
 * This service handles:
 * - Scheduling deletion of anonymous lists
 * - Claiming anonymous lists for registered users
 * - Managing deletion job lifecycle
 */
class ListClaimService
{
    /**
     * The delay before an unclaimed list is deleted.
     */
    private const DELETION_DELAY_MINUTES = 30;

    /**
     * Schedule a list for deletion if it remains unclaimed.
     *
     * This method dispatches a DeleteUnclaimedList job that will execute
     * after the configured delay unless the list is claimed.
     *
     * @param  DecisionList  $list  The list to schedule for deletion
     */
    public function scheduleForDeletion(DecisionList $list): void
    {
        Log::info('Starting scheduleForDeletion process', [
            'list_id' => $list->id,
            'is_anonymous' => $list->is_anonymous,
            'claimed_at' => $list->claimed_at,
            'queue_connection' => config('queue.default'),
            'queue_driver' => config('queue.connections.' . config('queue.default') . '.driver'),
        ]);

        if (! $list->is_anonymous) {
            Log::warning('Attempted to schedule non-anonymous list for deletion', [
                'list_id' => $list->id,
            ]);
            return;
        }

        try {
            Log::info('Preparing to dispatch DeleteUnclaimedList job', [
                'list_id' => $list->id,
                'scheduled_deletion_time' => Carbon::now()->addMinutes(self::DELETION_DELAY_MINUTES),
                'queue' => 'deletions',
            ]);

            $job = DeleteUnclaimedList::dispatch($list)
                ->delay(Carbon::now()->addMinutes(self::DELETION_DELAY_MINUTES))
                ->onQueue('deletions');

            Log::info('DeleteUnclaimedList job dispatched successfully', [
                'list_id' => $list->id,
                'job_dispatched' => $job !== null,
                'queue' => 'deletions',
                'scheduled_time' => Carbon::now()->addMinutes(self::DELETION_DELAY_MINUTES),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch DeleteUnclaimedList job', [
                'list_id' => $list->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Claim an anonymous list for a user.
     *
     * This method:
     * 1. Associates the list with the user
     * 2. Marks the list as claimed
     * 3. Removes the anonymous flag
     * 4. Associates any anonymous votes with the user
     *
     * All operations are performed in a transaction to ensure data consistency.
     *
     * @param  DecisionList  $list  The list to claim
     * @param  User  $user  The user claiming the list
     * @return DecisionList The updated list
     *
     * @throws \RuntimeException If the list is already claimed or not anonymous
     */
    public function claimList(DecisionList $list, User $user): DecisionList
    {
        Log::info('Starting claimList process', [
            'list_id' => $list->id,
            'user_id' => $user->id,
            'is_anonymous' => $list->is_anonymous,
            'claimed_at' => $list->claimed_at,
        ]);

        if (! $list->is_anonymous) {
            Log::error('Attempted to claim non-anonymous list', [
                'list_id' => $list->id,
                'user_id' => $user->id,
            ]);
            throw new \RuntimeException('Cannot claim a non-anonymous list');
        }

        if ($list->claimed_at !== null) {
            Log::error('Attempted to claim already claimed list', [
                'list_id' => $list->id,
                'user_id' => $user->id,
                'existing_claimed_at' => $list->claimed_at,
            ]);
            throw new \RuntimeException('List is already claimed');
        }

        return DB::transaction(function () use ($list, $user) {
            Log::info('Starting transaction to claim list', [
                'list_id' => $list->id,
                'user_id' => $user->id,
            ]);

            // Update the list
            $list->user_id = $user->id;
            $list->is_anonymous = false;
            $list->claimed_at = now();
            $list->save();

            Log::info('List updated with user ownership', [
                'list_id' => $list->id,
                'user_id' => $user->id,
                'claimed_at' => $list->claimed_at,
            ]);

            // Associate anonymous votes with the user
            $matchupIds = $list->matchups()->pluck('id');
            $sessionToken = session()->getId();
            
            Log::info('Associating anonymous votes with user', [
                'list_id' => $list->id,
                'user_id' => $user->id,
                'matchup_count' => $matchupIds->count(),
                'session_token' => $sessionToken,
            ]);

            Vote::whereIn('matchup_id', $matchupIds)
                ->whereNull('user_id')
                ->where('session_token', $sessionToken)
                ->update(['user_id' => $user->id]);

            Log::info('List successfully claimed by user', [
                'list_id' => $list->id,
                'user_id' => $user->id,
                'session_token' => $sessionToken,
            ]);

            return $list;
        });
    }
}
