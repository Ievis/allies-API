<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\Problem;
use App\Models\User;
use App\Services\LessonAccessService;
use App\Services\UserService;
use Illuminate\Auth\Access\Response;

class ProblemPolicy
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
    public function view(User $user, Problem $problem): Response
    {
        $lesson = $problem->lesson()->firstOrFail();
        $course_id = $lesson->course()->firstOrFail()->id;

        return (
            LessonAccessService::authStudent($user, $lesson)
            or LessonAccessService::authTeacher($user, $course_id)
            or UserService::isAdmin($user)
        )
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        $lesson = Lesson::findOrFail(request('lesson_id'));
        $course_id = $lesson->course()->firstOrFail()->id;

        return (
            LessonAccessService::authTeacher($user, $course_id)
            or UserService::isAdmin($user)
        )
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Problem $problem): Response
    {
        $lesson = $problem->lesson()->firstOrFail();
        $course_id = $lesson->course()->firstOrFail()->id;

        return (
            LessonAccessService::authTeacher($user, $course_id)
            or UserService::isAdmin($user)
        )
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Problem $problem): Response
    {
        $lesson = $problem->lesson()->firstOrFail();
        $course_id = $lesson->course()->firstOrFail()->id;

        return (
            LessonAccessService::authTeacher($user, $course_id)
            or UserService::isAdmin($user)
        )
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Problem $problem): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Problem $problem): bool
    {
        //
    }
}
