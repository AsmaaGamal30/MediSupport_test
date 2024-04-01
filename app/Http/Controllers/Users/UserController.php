<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return UserResource::collection(User::query()->paginate(10));
    }

    public function getFirstEightUsers()
    {
        if (!auth()->guard('admin')->user()) {
            return $this->error('You Are Not authorized', 401);
        }
        $users = User::take(8)->get();

        return UserResource::collection($users);
    }

    public function getUsersCount()
    {
        if (!auth()->guard('admin')->user()) {
            return $this->error('You Are Not authorized', 401);
        }
        $userCount = User::count();

        return $this->sendData('Number of Users', $userCount);
    }
}
