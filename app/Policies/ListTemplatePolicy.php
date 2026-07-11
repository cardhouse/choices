<?php

namespace App\Policies;

use App\Models\ListTemplate;
use App\Models\User;

class ListTemplatePolicy
{
    /**
     * Determine whether the user can view the template.
     */
    public function view(User $user, ListTemplate $template): bool
    {
        return $template->isOwnedBy($user);
    }

    /**
     * Determine whether the user can create templates.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the template.
     */
    public function update(User $user, ListTemplate $template): bool
    {
        return $template->isOwnedBy($user);
    }

    /**
     * Determine whether the user can delete the template.
     */
    public function delete(User $user, ListTemplate $template): bool
    {
        return $template->isOwnedBy($user);
    }
}
