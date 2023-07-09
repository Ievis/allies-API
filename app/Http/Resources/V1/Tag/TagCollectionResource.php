<?php

namespace App\Http\Resources\V1\Tag;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TagCollectionResource extends ResourceCollection
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

        return $this->collection->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name
            ];
        })->toArray();
    }
}
