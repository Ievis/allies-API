<?php

namespace App\Http\Resources\V1\Modification;

use App\Models\Modification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ModificationCollectionResource extends ResourceCollection
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

        return $this->collection->map(function ($modification) {
            return [
                'id' => $modification->id,
                'name' => $modification->name,
                'for' => Modification::NAMING_EN[$modification->modifiable_type]
            ];
        })->toArray();
    }
}
