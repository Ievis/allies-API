<?php

namespace App\Http\Resources\V1\Course;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'name' => $this->name,
            'preview' => $this->preview,
            'category_id' => $this->category_id,
            'subject_id' => $this->subject_id,
            'price' => $this->price
        ];
    }
}
