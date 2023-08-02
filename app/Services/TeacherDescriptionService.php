<?php

namespace App\Services;

use App\Models\TeacherDescription;

class TeacherDescriptionService
{
    public static function createTeacherDescription(array $data): TeacherDescription
    {
        return TeacherDescription::create($data);
    }

    public static function updateTeacherDescription(array $data, TeacherDescription $teacher_description): TeacherDescription
    {
        $teacher_description->update($data);

        return $teacher_description;
    }

    public static function deleteTeacherDescription(TeacherDescription $teacher_description): TeacherDescription
    {
        $teacher_description->delete();

        return $teacher_description;
    }
}
