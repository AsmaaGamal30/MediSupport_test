<?php

namespace App\Services\Auth;

use Validator;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthService
{
    public function login($request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return ['error' => $validator->errors(), 'code' => 422];
        }
        if (!$token = auth()->guard('admin')->attempt($request->validated())) {
            return ['error' => 'Invalid email or password', 'code' => 401];
        }
        return $token;
    }

    public function logout()
    {
        auth()->guard('admin')->logout();
    }

    public function refreshToken()
    {
        return auth()->guard('admin')->refresh();
    }

    public function getAuthenticatedAdmin()
    {
        return auth()->guard('admin')->user();
    }

    public function updatePassword($request)
    {
        if (!Auth::guard('admin')->check()) {
            return ['error' => 'Unauthenticated', 'code' => 401];
        }

        try {
            $request->validate([
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $admin = Auth::guard('admin')->user();

            // Verify if the current password matches the one in the database
            if (!Hash::check($request->current_password, $admin->password)) {
                throw ValidationException::withMessages(['current_password' => ['Current password does not match']]);
            }

            $admin->password = Hash::make($request->new_password);
            $admin->save();

            return true;
        } catch (ValidationException $e) {
            return ['error' => $e->getMessage(), 'code' => 422];
        }
    }
}
