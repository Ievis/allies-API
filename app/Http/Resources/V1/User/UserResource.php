<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
        parent::wrap('data');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'vk_id' => $this->vk_id,
            'image' => $this->image,
        ];
    }
}
