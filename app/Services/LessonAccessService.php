<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class LessonAccessService
{
    public static function authStudent(User $user, Lesson $lesson): bool
    {
        $course = $lesson->course()->first();
        if (empty($course->is_visible)) return false;

        // TODO: учесть, что курс может быть бесплатным

        $course_user = DB::table('course_user')
            ->where('user_id', $user->id)
            ->where('course_id', $lesson->course->id)
            ->first();

        $now = Carbon::now();
        $expires_at = empty($course_user)
            ? $now->toDate()
            : $course_user->expires_at;

        return $now->lt($expires_at) or $course_user->is_annual;
    }

    public static function authTeacher(User $user, $course_id): bool
    {
        if (empty($course_id)) return false;

        return (isset(DB::table('course_user')
                ->where('user_id', $user->id)
                ->where('course_id', $course_id)
                ->where('is_teacher', true)
                ->first()
                ->is_teacher));
    }

    public static function authMainTeacher(User $user, $course_id)
    {
        if (empty($course_id)) return false;

        return (isset(DB::table('course_user')
                ->where('user_id', $user->id)
                ->where('course_id', $course_id)
                ->where('is_main_teacher', true)
                ->first()
                ->is_teacher));
    }
}
