<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\UpdateUserRequest;
use App\Http\Requests\AuthRequests\UserLoginRequest;
use App\Http\Requests\AuthRequests\UserRegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Services\Auth\UserAuthService;
use App\Traits\ApiResponse;

class UserAuthController extends Controller
{
    use ApiResponse;

    protected $userAuthService;

    public function __construct(UserAuthService $userAuthService)
    {
        $this->middleware('auth:user', ['except' => ['login', 'register']]);
        $this->userAuthService = $userAuthService;
    }

    public function login(UserLoginRequest $request)
    {
        $result = $this->userAuthService->login($request);
        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code']);
        }
        return $this->createNewToken($result);
    }

    public function register(UserRegisterRequest $request)
    {
        $result = $this->userAuthService->register($request);
        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code']);
        }
        return $this->success('User successfully registered', 201);
    }

    public function logout()
    {
        $this->userAuthService->logout();
        return $this->success('User successfully signed out');
    }

    public function refresh()
    {
        $token = $this->userAuthService->refreshToken();
        return $this->createNewToken($token);
    }

    public function userProfile()
    {
        $user = $this->userAuthService->getAuthenticatedUser();
        return $this->sendData('', new UserResource($user));
    }

    public function updateUser(UpdateUserRequest $request)
    {
        $result = $this->userAuthService->updateUser($request);
        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code']);
        }
        return $this->success('User updated successfully');
    }

    public function deleteAccount()
    {
        $this->userAuthService->deleteAccount();
        return $this->success('User account deleted successfully');
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => new UserResource($this->userAuthService->getAuthenticatedUser())
        ]);
    }
}
