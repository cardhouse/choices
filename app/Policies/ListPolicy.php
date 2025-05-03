<?php

namespace App\Policies;

use App\Models\DecisionList;
use App\Models\User;

class ListPolicy
{
    /**
     * Determine whether the user can view the list.
     */
    public function view(User $user, DecisionList $list): bool
    {
        // Allow viewing if:
        // 1. User owns the list
        // 2. List is unclaimed (anonymous)
        // 3. List has an active share code
        return $user->id === $list->user_id
            || ($list->is_anonymous && ! $list->claimed_at)
            || $list->shareCodes()->active()->exists();
    }

    /**
     * Determine whether the user can create lists.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create lists
        return true;
    }

    /**
     * Determine whether the user can update the list.
     */
    public function update(User $user, DecisionList $list): bool
    {
        // Only the owner can update the list
        return $user->id === $list->user_id;
    }

    /**
     * Determine whether the user can delete the list.
     */
    public function delete(User $user, DecisionList $list): bool
    {
        // Only the owner can delete the list
        return $user->id === $list->user_id;
    }

    /**
     * Determine whether the user can claim the list.
     */
    public function claim(User $user, DecisionList $list): bool
    {
        // Can claim if:
        // 1. List is anonymous
        // 2. List is not already claimed
        return $list->is_anonymous && $list->claimed_at === null;
    }
}
