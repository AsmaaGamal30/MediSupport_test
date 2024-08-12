<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\DoctorLoginRequest;
use App\Http\Requests\AuthRequests\DoctorRegisterRequest;
use App\Http\Resources\Doctor\DoctorResource;
use App\Services\Auth\DoctorAuthService;
use App\Traits\ApiResponse;

class DoctorAuthController extends Controller
{
    use ApiResponse;

    protected $doctorAuthService;

    public function __construct(DoctorAuthService $doctorAuthService)
    {
        $this->middleware('auth:doctor', ['except' => ['login', 'register']]);
        $this->doctorAuthService = $doctorAuthService;
    }

    public function login(DoctorLoginRequest $request)
    {
        $result = $this->doctorAuthService->login($request);
        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code']);
        }
        return $this->createNewToken($result);
    }

    public function register(DoctorRegisterRequest $request)
    {
        $result = $this->doctorAuthService->register($request);
        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code']);
        }
        return $this->success('Doctor successfully registered', 201);
    }

    public function logout()
    {
        $this->doctorAuthService->logout();
        return $this->success('Doctor successfully signed out');
    }

    public function refresh()
    {
        $token = $this->doctorAuthService->refreshToken();
        return $this->createNewToken($token);
    }

    public function userProfile()
    {
        $doctor = $this->doctorAuthService->getAuthenticatedDoctor();
        return $this->sendData('', new DoctorResource($doctor));
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'doctor' => new DoctorResource($this->doctorAuthService->getAuthenticatedDoctor()),
        ]);
    }
}
