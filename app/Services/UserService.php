<?php

namespace App\Services;

use App\Exceptions\BadCredentionals;
use App\Http\Resources\V1\UserResource;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public static function createUser(array $data): User
    {
        $image = FileService::save($data['image'] ?? null);

        $data = array_merge($data, [
            'image' => $image,
            'password' => (empty($data['password']))
                ? null
                : Hash::make($data['password'])
        ]);

        return User::create($data);
    }

    public static function updateUser(User $user, array $data): User
    {
        $data['password'] = empty($data['password']) ? null : Hash::make($data['password']);
        $data['image'] = empty($data['image'])
            ? $user->image
            : FileService::save($data['image']);
        $user->update($data);

        return $user;
    }

    public static function deleteUser(User $user): User
    {
        $user->delete();

        return $user;
    }

    public static function isAdmin(?User $user): bool
    {
        return !empty($user) && $user->role->name === 'admin';
    }

    public static function isNotAdmin(User $user): bool
    {
        return !self::isAdmin($user);
    }
}
