<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function update(User $authenticatedUser, User $userToUpdate)
    {
        return $authenticatedUser->id === $userToUpdate->id;
    }
}