<?php

namespace App\Services;

use App\Models\User;

class UserAccessService
{
    public static function authTeacher(User $teacher, User $user): bool
    {
        $courses = $teacher->courses()
            ->where('is_teacher', true)
            ->get();
        $user_ids = collect();
        foreach ($courses as $course) {
            $ids = $course->users()->get()->pluck('id');
            $user_ids->push($ids);
        }
        $user_ids = $user_ids->collapse()->toArray();

        return in_array($user->id, $user_ids);
    }

    public static function authStudent(User $student, User $user): bool
    {
        return $student->id === $user->id;
    }
}
