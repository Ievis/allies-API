<?php

namespace App\Services;

use App\Models\Problem;

class ProblemService
{
    public static function createProblem(array $data): Problem
    {
        $modification_preparation = new PreModificationService($data, Problem::class);
        $modification_preparation->createPreparation();

        $modification_service = new ModificationService(
            $modification_preparation->getData(),
            $modification_preparation->getUser(),
            $modification_preparation->getModel()
        );

        $modification_service->createModification();

        $course_id = $modification_preparation->getModel()->lesson()->first()->course()->first()->id;
        if (UserService::isAdmin($modification_preparation->getUser())
            or LessonAccessService::authMainTeacher($modification_preparation->getUser(), $course_id)) {
            return $modification_service->resolveModification();
        }

        return $modification_preparation->getModel();
    }

    public static function updateProblem(Problem $old_problem, array $data): Problem
    {
        $modification_preparation = new PreModificationService($data, Problem::class);
        $modification_preparation->updatePreparation($old_problem);

        $modification_service = new ModificationService(
            $modification_preparation->getData(),
            $modification_preparation->getUser(),
            $modification_preparation->getModel(),
            $modification_preparation->getOldModel()->id
        );
        $modification_service->createModification();

        $course_id = $modification_preparation->getModel()->lesson()->first()->course()->first()->id;
        if (UserService::isAdmin($modification_preparation->getUser())
            or LessonAccessService::authMainTeacher($modification_preparation->getUser(), $course_id)) {
            return $modification_service->resolveModification();
        }

        return $modification_preparation->getModel();
    }

    public static function deleteProblem(Problem $old_problem)
    {
        $user = auth('api')->user();

        $modification_service = new ModificationService([], $user, $old_problem);
        $modification_service->createModification();

        $course_id = $old_problem->lesson()->first()->course()->first()->id;
        if (UserService::isAdmin($user)
            or LessonAccessService::authMainTeacher($user, $course_id)) {
            return $modification_service->resolveModification();
        }

        return $old_problem;
    }
}
