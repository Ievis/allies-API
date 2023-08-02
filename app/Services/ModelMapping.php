<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Problem;

class ModelMapping
{
    private $model_mapping;
    private $model;

    public function __construct(Lesson|Problem|string $model)
    {
        $this->model = is_object($model)
            ? get_class($model)
            : $model;

        $this->model_mapping = [
            Lesson::class => [
                'table' => 'lessons',
                'model_id' => 'course_id',
                'model_number' => 'number_in_course',
            ],
            Problem::class => [
                'table' => 'problems',
                'model_id' => 'lesson_id',
                'model_number' => 'number_in_lesson',
            ]
        ];
    }

    public function getModelMapping()
    {
        return $this->model_mapping[$this->model];
    }
}
