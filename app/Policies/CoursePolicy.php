<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    public function create(User $user): Response
    {
        return UserService::isAdmin($user)
            ? Response::allow()
            : Response::deny();
    }

    public function update(User $user, Course $course): Response
    {
        return UserService::isAdmin($user)
            ? Response::allow()
            : Response::deny();
    }

    public function delete(User $user, Course $course): Response
    {
        return UserService::isAdmin($user)
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Course $course): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        //
    }
}
