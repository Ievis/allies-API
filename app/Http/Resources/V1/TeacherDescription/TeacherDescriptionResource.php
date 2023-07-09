<?php

namespace App\Http\Resources\V1\TeacherDescription;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherDescriptionResource extends JsonResource
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
            'description' => $this->description,
        ];
    }
}
