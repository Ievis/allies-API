<?php

namespace App\Policies;

use App\Models\TeacherDescription;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Access\Response;

class TeacherDescriptionPolicy
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
    public function view(User $user, TeacherDescription $teacherDescription): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return UserService::isAdmin($user)
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TeacherDescription $teacherDescription): Response
    {
        return UserService::isAdmin($user)
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TeacherDescription $teacherDescription): Response
    {
        return UserService::isAdmin($user)
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TeacherDescription $teacherDescription): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TeacherDescription $teacherDescription): bool
    {
        //
    }
}
