<?php

namespace App\Http\Resources\V1\TeacherDescription;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TeacherDescriptionCollectionResource extends ResourceCollection
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

        return $this->collection->map(function ($teacherDescription) {
            return [
                'id' => $teacherDescription->id,
                'description' => $teacherDescription->description,
            ];
        })->toArray();
    }
}
