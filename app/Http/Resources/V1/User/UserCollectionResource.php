<?php

namespace App\Http\Resources\V1\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollectionResource extends ResourceCollection
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

        return $this->collection->map(function ($user) {
            $role = User::getRoleById($user->role_id);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $role,
                'email' => $user->email,
                'image' => $user->image,
            ];
        })->toArray();
    }
}
