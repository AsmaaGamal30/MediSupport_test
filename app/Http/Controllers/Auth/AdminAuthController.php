<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\AuthRequests\AdminLoginResquest;
use App\Http\Requests\AuthRequests\UpdateAdminRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Services\Auth\AdminAuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminAuthController extends Controller
{
    use ApiResponse;

    protected $adminAuthService;

    public function __construct(AdminAuthService $adminAuthService)
    {
        $this->middleware('auth:admin', ['except' => ['login']]);
        $this->adminAuthService = $adminAuthService;
    }

    public function login(AdminLoginResquest $request)
    {
        $result = $this->adminAuthService->login($request);
        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code']);
        }
        return $this->createNewToken($result);
    }

    public function logout()
    {
        $this->adminAuthService->logout();
        return $this->success('Admin successfully signed out');
    }

    public function refresh()
    {
        $token = $this->adminAuthService->refreshToken();
        return $this->createNewToken($token);
    }

    public function userProfile()
    {
        $admin = $this->adminAuthService->getAuthenticatedAdmin();
        return $this->sendData('', new AdminResource($admin));
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'admin' => new AdminResource($this->adminAuthService->getAuthenticatedAdmin()),
        ]);
    }

    public function updatePassword(UpdateAdminRequest $request)
    {
        $result = $this->adminAuthService->updatePassword($request);
        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code']);
        }
        return $this->success('Password updated successfully');
    }
}
