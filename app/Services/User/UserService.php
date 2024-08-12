<?php

namespace App\Services\User;

use App\Http\Resources\User\UserResource;
use App\Models\User;

class UserService
{
    public function getPaginatedUsers()
    {
        return UserResource::collection(User::query()->paginate(10));
    }

    public function getFirstEightUsers()
    {
        $users = User::take(8)->get();
        return [
            'data' => UserResource::collection($users),
            'status' => 200
        ];
    }

    public function getUsersCount()
    {
        $userCount = User::count();
        return [
            'data' => ['Number of Users' => $userCount],
            'status' => 200
        ];
    }
}
