<?php

namespace App\Actions\Sharing;

use App\Exceptions\InvalidShareCodeException;
use App\Models\DecisionList;
use App\Models\ShareCode;
use App\Models\User;

/**
 * Turns a share code into list access: the user becomes a participant of the
 * list the code belongs to.
 */
class RedeemShareCode
{
    /**
     * @throws InvalidShareCodeException If the code is unknown, expired, or revoked
     */
    public function handle(string $code, User $user): DecisionList
    {
        $shareCode = ShareCode::active()
            ->where('code', strtoupper(trim($code)))
            ->first();

        if ($shareCode === null) {
            throw InvalidShareCodeException::notFound();
        }

        $list = $shareCode->list;

        if ($list->isVotingClosed()) {
            throw InvalidShareCodeException::notFound();
        }

        if (! $list->isOwnedBy($user)) {
            $list->participants()->firstOrCreate(
                ['user_id' => $user->id],
                ['share_code_id' => $shareCode->id],
            );
        }

        return $list;
    }
}
