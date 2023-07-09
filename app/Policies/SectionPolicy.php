<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Section;
use App\Models\User;
use App\Services\LessonAccessService;
use App\Services\UserService;
use Illuminate\Auth\Access\Response;

class SectionPolicy
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
    public function view(User $user, Section $section): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        $course = Course::findOrFail(request()->input('course_id'));
        $course_id = $course->id;

        return (
            UserService::isAdmin($user)
            or LessonAccessService::authMainTeacher($user, $course_id)
        )
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Section $section): Response
    {
        $course = $section->course()->get();
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
    public function delete(User $user, Section $section): Response
    {
        $course = $section->course()->get();
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
    public function restore(User $user, Section $section): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Section $section): bool
    {
        //
    }
}
