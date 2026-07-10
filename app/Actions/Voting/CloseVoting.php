<?php

namespace App\Actions\Voting;

use App\Models\DecisionList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Closes voting on a list and deactivates its share codes so the code can
 * no longer be redeemed. Idempotent: closing an already-closed list is a no-op.
 */
class CloseVoting
{
    public function handle(DecisionList $list): DecisionList
    {
        if ($list->voting_closed_at !== null) {
            return $list;
        }

        DB::transaction(function () use ($list) {
            $list->voting_closed_at = now();
            $list->save();

            $list->shareCodes()
                ->whereNull('deactivated_at')
                ->update(['deactivated_at' => now()]);
        });

        Log::info('Voting closed on list', ['list_id' => $list->id]);

        return $list;
    }
}
