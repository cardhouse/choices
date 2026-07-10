<?php

namespace App\Actions\Lists;

use App\Jobs\DeleteUnclaimedList;
use App\Models\DecisionList;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Queues deletion of an anonymous list; the job skips lists claimed in the meantime.
 */
class ScheduleListDeletion
{
    /**
     * The delay before an unclaimed list is deleted.
     */
    public const DELETION_DELAY_MINUTES = 30;

    public function handle(DecisionList $list): void
    {
        if (! $list->is_anonymous) {
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
        ]);
    }
}
