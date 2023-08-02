<?php

namespace App\Http\Resources\V1\Lesson;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
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

        return [
            'id' => $this->id,
            'number_in_course' => $this->number_in_course,
            'url' => $this->url,
            'zoom_url' => $this->zoom_url,
            'will_at' => $this->will_at,
            'description' => $this->description,
            'course_id' => $this->course_id,
            'type_id' => $this->type_id,
            'status_id' => $this->status_id,
        ];
    }
}
