<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\Modification;
use App\Models\User;
use App\Services\LessonAccessService;
use App\Services\UserService;
use Illuminate\Auth\Access\Response;

class ModificationPolicy
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
    public function view(User $user, Modification $modification): Response
    {
        $model = $modification->modifiable()->first();
        $course = $model::class === Lesson::class
            ? $model->course()->first()
            : $model->lesson()->first()->course()->first();
        $course_id = $course->id;

        return (
            UserService::isAdmin($user)
            or LessonAccessService::authMainTeacher($user, $course_id)
        )
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Modification $modification): Response
    {
        $model = $modification->modifiable()->first();
        $course = $model::class === Lesson::class
            ? $model->course()->first()
            : $model->lesson()->first()->course()->first();
        $course_id = $course->id;

        return (
            UserService::isAdmin($user)
            or LessonAccessService::authMainTeacher($user, $course_id)
        )
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Modification $modification): Response
    {
        $model = $modification->modifiable()->first();
        $course = $model::class === Lesson::class
            ? $model->course()->first()
            : $model->lesson()->first()->course()->first();
        $course_id = $course->id;

        return (
            UserService::isAdmin($user)
            or LessonAccessService::authMainTeacher($user, $course_id)
        )
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Modification $modification): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Modification $modification): bool
    {
        //
    }
}
