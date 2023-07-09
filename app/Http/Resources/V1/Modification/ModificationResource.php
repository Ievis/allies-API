<?php

namespace App\Http\Resources\V1\Modification;

use App\Models\Modification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModificationResource extends JsonResource
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
            'for' => Modification::NAMING_EN[$this->modifiable_type]
        ];
    }
}
