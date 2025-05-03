<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use App\Policies\ListPolicy;

class ItemPolicy
{
    /**
     * Determine whether the user can view the item.
     */
    public function view(User $user, Item $item): bool
    {
        // Allow viewing if the user can view the parent list
        $listPolicy = new ListPolicy();
        return $listPolicy->view($user, $item->list);
    }

    /**
     * Determine whether the user can create items.
     */
    public function create(User $user, Item $item): bool
    {
        // Allow creation if the user can update the parent list
        $listPolicy = new ListPolicy();
        return $listPolicy->update($user, $item->list);
    }

    /**
     * Determine whether the user can update the item.
     */
    public function update(User $user, Item $item): bool
    {
        // Allow update if the user can update the parent list
        $listPolicy = new ListPolicy();
        return $listPolicy->update($user, $item->list);
    }

    /**
     * Determine whether the user can delete the item.
     */
    public function delete(User $user, Item $item): bool
    {
        // Allow deletion if the user can update the parent list
        $listPolicy = new ListPolicy();
        return $listPolicy->update($user, $item->list);
    }
}
