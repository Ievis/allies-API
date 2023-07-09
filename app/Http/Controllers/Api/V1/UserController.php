<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostUserRequest;
use App\Http\Resources\V1\User\UserCollectionResource;
use App\Http\Resources\V1\User\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): UserCollectionResource
    {
        $users = User::filter($request->all())->simplePaginateFilter($request->input('per_page') ?? User::count());

        return new UserCollectionResource($users);
    }

    public function store(PostUserRequest $request): UserResource
    {
        $data = $request->validated();
        $user = UserService::createUser($data);

        return new UserResource($user);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function update(PostUserRequest $request, User $user): UserResource
    {
        $data = $request->validated();
        $user = UserService::updateUser($user, $data);

        return new UserResource($user);
    }

    public function delete(User $user): UserResource
    {
        UserService::deleteUser($user);

        return new UserResource($user);
    }
}
