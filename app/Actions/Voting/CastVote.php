<?php

namespace App\Actions\Voting;

use App\Exceptions\InvalidVoteException;
use App\Exceptions\VotingClosedException;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\Vote;
use App\Support\Voter;

/**
 * Records (or changes) a voter's pick for one matchup. Votes are the source
 * of truth for results; matchups themselves are never mutated.
 */
class CastVote
{
    /**
     * @param  array{ip_address?: ?string, user_agent?: ?string}  $meta
     *
     * @throws InvalidVoteException If the chosen item is not part of the matchup
     * @throws VotingClosedException If voting has closed on the list
     */
    public function handle(Voter $voter, Matchup $matchup, DecisionListItem $chosenItem, array $meta = []): Vote
    {
        if (! $matchup->involves($chosenItem->id)) {
            throw new InvalidVoteException('The chosen item is not part of this matchup.');
        }

        $list = $matchup->list()->first();

        if ($list->isVotingClosed()) {
            throw VotingClosedException::forList($list->id);
        }

        return Vote::updateOrCreate(
            [
                'matchup_id' => $matchup->id,
                'user_id' => $voter->userId,
                'session_token' => $voter->sessionToken,
            ],
            [
                'chosen_item_id' => $chosenItem->id,
                'ip_address' => $meta['ip_address'] ?? null,
                'user_agent' => $meta['user_agent'] ?? null,
            ],
        );
    }
}
