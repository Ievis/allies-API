<?php

namespace App\Services;

use App\Models\Subject;

class SubjectService
{
    public static function createSubject(array $data): Subject
    {
        return Subject::create($data);
    }

    public static function updateSubject(Subject $subject, array $data): Subject
    {
        $subject->update($data);

        return $subject;
    }
}
