<?php

namespace App\Actions\Lists;

use App\Models\DecisionList;
use Illuminate\Support\Facades\Log;

/**
 * Deletes a list; items, matchups, votes, share codes, and participants
 * cascade at the database level.
 */
class DeleteList
{
    public function handle(DecisionList $list): void
    {
        $listId = $list->id;

        $list->delete();

        Log::info('Deleted list', ['list_id' => $listId]);
    }
}
