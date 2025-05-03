<?php

namespace App\Services;

use App\Jobs\DeleteUnclaimedList;
use App\Models\DecisionList;
use App\Models\User;
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
     * @param DecisionList $list The list to schedule for deletion
     * @return void
     */
    public function scheduleForDeletion(DecisionList $list): void
    {
        if (!$list->is_anonymous) {
            Log::warning('Attempted to schedule non-anonymous list for deletion', [
                'list_id' => $list->id,
            ]);
            return;
        }

        DeleteUnclaimedList::dispatch($list)
            ->delay(Carbon::now()->addMinutes(self::DELETION_DELAY_MINUTES))
            ->onQueue('deletions');

        Log::info('Scheduled list for deletion', [
            'list_id' => $list->id,
            'scheduled_deletion' => Carbon::now()->addMinutes(self::DELETION_DELAY_MINUTES),
        ]);
    }

    /**
     * Claim an anonymous list for a user.
     * 
     * This method:
     * 1. Associates the list with the user
     * 2. Marks the list as claimed
     * 3. Removes the anonymous flag
     * 
     * All operations are performed in a transaction to ensure data consistency.
     *
     * @param DecisionList $list The list to claim
     * @param User $user The user claiming the list
     * @return DecisionList The updated list
     * 
     * @throws \RuntimeException If the list is already claimed or not anonymous
     */
    public function claimList(DecisionList $list, User $user): DecisionList
    {
        if (!$list->is_anonymous) {
            throw new \RuntimeException('Cannot claim a non-anonymous list');
        }

        if ($list->claimed_at !== null) {
            throw new \RuntimeException('List is already claimed');
        }

        return DB::transaction(function () use ($list, $user) {
            $list->user_id = $user->id;
            $list->is_anonymous = false;
            $list->claimed_at = now();
            $list->save();

            Log::info('List claimed by user', [
                'list_id' => $list->id,
                'user_id' => $user->id,
            ]);

            return $list;
        });
    }
} 