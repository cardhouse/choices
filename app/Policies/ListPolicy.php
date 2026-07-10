<?php

namespace App\Policies;

use App\Models\DecisionList;
use App\Models\User;

class ListPolicy
{
    /**
     * Determine whether the user (or guest) can view the list.
     */
    public function view(?User $user, DecisionList $list): bool
    {
        // Unclaimed anonymous lists are visible to their (guest) creator;
        // there is no ownership record, so anyone with the URL qualifies.
        if ($list->is_anonymous && ! $list->claimed_at) {
            return true;
        }

        return $list->isOwnedBy($user) || $list->hasParticipant($user);
    }

    /**
     * Determine whether the user can create lists.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the list.
     */
    public function update(User $user, DecisionList $list): bool
    {
        return $list->isOwnedBy($user);
    }

    /**
     * Determine whether the user can delete the list.
     */
    public function delete(User $user, DecisionList $list): bool
    {
        return $list->isOwnedBy($user);
    }

    /**
     * Determine whether the user can claim the list.
     */
    public function claim(User $user, DecisionList $list): bool
    {
        return $list->is_anonymous && $list->claimed_at === null;
    }

    /**
     * Determine whether the user (or guest) can vote on the list.
     */
    public function vote(?User $user, DecisionList $list): bool
    {
        if ($list->isVotingClosed()) {
            return false;
        }

        if ($list->is_anonymous && ! $list->claimed_at) {
            return true;
        }

        return $list->isOwnedBy($user) || $list->hasParticipant($user);
    }

    /**
     * Determine whether the user can view the list results.
     *
     * The owner can always peek; everyone else waits until voting closes.
     */
    public function viewResults(User $user, DecisionList $list): bool
    {
        if ($list->isOwnedBy($user)) {
            return true;
        }

        return $list->isVotingClosed() && $list->hasParticipant($user);
    }

    /**
     * Determine whether the user can share the list or close its voting.
     */
    public function manageVoting(User $user, DecisionList $list): bool
    {
        return $list->isOwnedBy($user);
    }
}
