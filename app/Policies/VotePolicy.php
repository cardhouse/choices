<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vote;

class VotePolicy
{
    /**
     * Determine whether the user can view the vote.
     */
    public function view(User $user, Vote $vote): bool
    {
        // Allow viewing if the user can view the parent list
        $listPolicy = new ListPolicy;

        return $listPolicy->view($user, $vote->matchup->list);
    }

    /**
     * Determine whether the user can create votes.
     */
    public function create(User $user, Vote $vote): bool
    {
        // Allow creation if:
        // 1. User can view the parent list
        // 2. Chosen item belongs to the matchup
        $listPolicy = new ListPolicy;

        return $listPolicy->view($user, $vote->matchup->list)
            && ($vote->chosen_item_id === $vote->matchup->item_a_id
                || $vote->chosen_item_id === $vote->matchup->item_b_id);
    }

    /**
     * Determine whether the user can update the vote.
     */
    public function update(User $user, Vote $vote): bool
    {
        // Allow update if:
        // 1. User owns the vote
        // 2. Chosen item belongs to the matchup
        return $user->id === $vote->user_id
            && ($vote->chosen_item_id === $vote->matchup->item_a_id
                || $vote->chosen_item_id === $vote->matchup->item_b_id);
    }

    /**
     * Determine whether the user can delete the vote.
     */
    public function delete(User $user, Vote $vote): bool
    {
        // Allow deletion if the user owns the vote
        return $user->id === $vote->user_id;
    }
}
