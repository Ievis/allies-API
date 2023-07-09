<?php

namespace App\Policies;

use App\Models\User;
use App\Services\UserAccessService;
use App\Services\UserService;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): Response
    {
        return (
            UserService::isAdmin($user)
            or UserAccessService::authTeacher($user, $model)
            or UserAccessService::authStudent($user, $model)
        )
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return (UserService::isAdmin($user))
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): Response
    {
        return (
            UserService::isAdmin($user)
            or UserAccessService::authStudent($user, $model)
        )
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response
    {
        return (UserService::isAdmin($user) && $user->id !== $model->id)
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        //
    }
}
