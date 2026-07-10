<?php

namespace App\Actions\Sharing;

use App\Models\DecisionList;

/**
 * Deactivates a list's active share codes so no one else can join. Existing
 * participants keep their access.
 */
class RevokeShareCode
{
    public function handle(DecisionList $list): void
    {
        $list->shareCodes()
            ->whereNull('deactivated_at')
            ->update(['deactivated_at' => now()]);
    }
}
