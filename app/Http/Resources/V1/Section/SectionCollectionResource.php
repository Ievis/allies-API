<?php

namespace App\Http\Resources\V1\Section;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SectionCollectionResource extends ResourceCollection
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

        return $this->collection->map(function ($section) {
            return [
                'id' => $section->id,
                'name' => $section->name
            ];
        })->toArray();
    }
}
