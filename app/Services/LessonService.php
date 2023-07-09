<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Modification;
use Illuminate\Support\Facades\DB;

class LessonService
{
    private static $lessons;
    private static $data;
    private static $total;

    public static function createLesson(array $data): Lesson
    {
        $modification_preparation = new PreModificationService($data, Lesson::class);
        $modification_preparation->createPreparation();

        $modification_service = new ModificationService(
            $modification_preparation->getData(),
            $modification_preparation->getUser(),
            $modification_preparation->getModel()
        );
        $modification_service->createModification();

        $course_id = $modification_preparation->getModel()->course()->first()->id;
        if (UserService::isAdmin($modification_preparation->getUser())
            or LessonAccessService::authMainTeacher($modification_preparation->getUser(), $course_id)) {
            return $modification_service->resolveModification();
        }

        return $modification_preparation->getModel();
    }

    public static function updateLesson(Lesson $old_lesson, array $data): Lesson
    {
        $modification_preparation = new PreModificationService($data, Lesson::class);
        $modification_preparation->updatePreparation($old_lesson);

        $modification_service = new ModificationService(
            $modification_preparation->getData(),
            $modification_preparation->getUser(),
            $modification_preparation->getModel(),
            $modification_preparation->getOldModel()->id
        );
        $modification_service->createModification();

        $course_id = $modification_preparation->getModel()->course()->first()->id;
        if (UserService::isAdmin($modification_preparation->getUser())
            or LessonAccessService::authMainTeacher($modification_preparation->getUser(), $course_id)) {
            return $modification_service->resolveModification();
        }

        return $modification_preparation->getModel();
    }

    public static function deleteLesson(Lesson $old_lesson)
    {
        $user = auth('api')->user();

        $modification_service = new ModificationService([], $user, $old_lesson);
        $modification_service->createModification();

        $course_id = $old_lesson->course()->first()->id;
        if (UserService::isAdmin($user)
            or LessonAccessService::authMainTeacher($user, $course_id)) {
            return $modification_service->resolveModification();
        }

        return $old_lesson;
    }
}
