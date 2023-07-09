<?php

namespace App\Http\Resources\V1\Lesson;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LessonCollectionResource extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->with = [
            'success' => true
        ];

        return $this->collection->map(function ($lesson) {
            return [
                'id' => $lesson->id,
                'number_in_course' => $lesson->number_in_course,
                'url' => $lesson->url,
                'zoom_url' => $lesson->zoom_url,
                'will_at' => $lesson->will_at,
                'description' => $lesson->description,
                'course_id' => $lesson->course_id,
                'type_id' => $lesson->type_id,
                'status_id' => $lesson->status_id,
            ];
        })->toArray();
    }
}
