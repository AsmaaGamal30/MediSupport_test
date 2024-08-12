<?php

namespace App\Services\Auth;

use Validator;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;

class DoctorAuthService
{
    public function login($request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return ['error' => $validator->errors(), 'code' => 422];
        }

        if (!$token = auth()->guard('doctor')->attempt($request->validated())) {
            return ['error' => 'Invalid email or password', 'code' => 401];
        }

        auth()->guard('doctor')->user()->update(['active_status' => 1]);

        return $token;
    }

    public function register($request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return ['error' => $validator->errors()->toJson(), 'code' => 400];
        }

        $avatarUrl = $request->file('avatar')->storeAs('avatar', $request->file('avatar')->getClientOriginalName(), 'public');

        $doctor = Doctor::create(array_merge(
            $request->validated(),
            [
                'password' => bcrypt($request->password),
                'avatar' => $avatarUrl
            ]
        ));

        return $doctor;
    }

    public function logout()
    {
        $doctor = auth()->guard('doctor')->user();
        if ($doctor) {
            $doctor->update(['active_status' => 0]);
        }
        auth()->guard('doctor')->logout();
    }

    public function refreshToken()
    {
        return auth()->guard('doctor')->refresh();
    }

    public function getAuthenticatedDoctor()
    {
        return auth()->guard('doctor')->user();
    }
}
