<?php

namespace App\Http\Resources\V1\Subject;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SubjectCollectionResource extends ResourceCollection
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

        return $this->collection->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name
            ];
        })->toArray();
    }
}
