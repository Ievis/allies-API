<?php

namespace App\Services;

use App\Models\Course;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseService
{
    public static function createCourse(array $data): Course
    {
        $data['preview'] = FileService::save($data['preview']);

        return Course::create($data);
    }

    public static function updateCourse(Course $course, array $data): Course
    {
        $data['preview'] = empty($data['preview'])
            ? $course->preview
            : FileService::save($data['preview']);
        $course->update($data);

        return $course;
    }

    public static function ensureIsVisible(Course $course): Course
    {
        if ($course->is_visible) return $course;

        $user = auth()->user();
        if (UserService::isAdmin($user) or $course->teachers()->contains($user)) {
            return $course;
        }

        throw new NotFoundHttpException();
    }
}
