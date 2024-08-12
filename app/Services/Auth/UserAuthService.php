<?php

namespace App\Services\Auth;

use Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserAuthService
{
    public function login($request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return ['error' => $validator->errors(), 'code' => 422];
        }

        if (!$token = auth()->guard('user')->attempt($request->validated())) {
            return ['error' => 'Invalid email or password', 'code' => 401];
        }

        auth()->guard('user')->user()->update(['active_status' => 1]);

        return $token;
    }

    public function register($request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return ['error' => $validator->errors()->toJson(), 'code' => 400];
        }

        $defaultAvatarPath = 'avatar/avatar.png';

        $user = User::create(array_merge(
            $request->validated(),
            [
                'password' => bcrypt($request->password),
                'avatar' => $defaultAvatarPath,
            ]
        ));

        return $user;
    }

    public function logout()
    {
        $user = auth()->guard('user')->user();
        if ($user) {
            $user->update(['active_status' => 0]);
        }
        auth()->guard('user')->logout();
    }

    public function refreshToken()
    {
        return auth()->guard('user')->refresh();
    }

    public function getAuthenticatedUser()
    {
        return auth()->guard('user')->user();
    }

    public function updateUser($request)
    {
        $user = auth()->guard('user')->user();
        if (!$user) {
            return ['error' => 'Unauthenticated user', 'code' => 401];
        }

        $validatedData = $request->validated();

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $avatarFile = $request->file('avatar');
            $avatarPath = $avatarFile->store('avatar', 'public');

            if ($user->avatar) {
                $oldAvatarPath = 'avatar/' . basename($user->avatar);
                Storage::disk('public')->delete($oldAvatarPath);
            }

            $validatedData['avatar'] = $avatarPath;
        }

        $user->update($validatedData);

        return $user;
    }

    public function deleteAccount()
    {
        $user = auth()->guard('user')->user();
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->delete();
        auth()->guard('user')->logout();
    }
}