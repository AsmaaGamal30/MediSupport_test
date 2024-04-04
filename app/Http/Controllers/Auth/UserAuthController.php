<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\UpdateUserRequest;
use App\Http\Requests\AuthRequests\UserLoginRequest;
use App\Http\Requests\AuthRequests\UserRegisterRequest;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Storage;
use Validator;

class UserAuthController extends Controller
{
    use ApiResponse;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:user', ['except' => ['login', 'register']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(UserLoginRequest $request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        if (!$token = auth()->guard('user')->attempt($request->validated())) {
            return $this->error('Invalid email or password', 401);
        }
        auth()->guard('user')->user()->update(['active_status' => 1]);
        return $this->createNewToken($token);
    }
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(UserRegisterRequest $request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return $this->error($validator->errors()->toJson(), 400);
        }

        $defaultAvatarPath = 'avatar/avatar.png';

        $user = User::create(array_merge(
            $request->validated(),
            [
                'password' => bcrypt($request->password),
                'avatar' => $defaultAvatarPath,
            ]
        ));
        return $this->success('User successfully registered', 201);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $user = auth()->guard('user')->user();
        if ($user) {
            $user->update(['active_status' => 0]);
        }
        auth()->guard('user')->logout();
        return $this->success('User successfully signed out');
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->guard('user')->refresh());
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function userProfile()
    {
        return $this->sendData('', new UserResource(auth()->guard('user')->user()));
    }

    public function updateUser(UpdateUserRequest $request)
    {
        try {
            $user = auth()->guard('user')->user();
            if (!$user) {
                return $this->error('Unauthenticated user', 401);
            }

            // Validate the request data
            $validatedData = $request->validated();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Validate file upload
                $request->validate([
                    'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Example validation rules, adjust as needed
                ]);

                // Get the uploaded file
                $avatarFile = $request->file('avatar');

                // Move the file to the desired directory
                $avatarPath = $avatarFile->store('avatar', 'public');

                // Delete the old avatar if it exists
                if ($user->avatar) {
                    $oldAvatarPath = 'avatar/' . basename($user->avatar);
                    Storage::disk('public')->delete($oldAvatarPath);
                }

                // Update the avatar field in the user model with the new path
                $validatedData['avatar'] = $avatarPath;
            }

            // Update other user data using the update method
            $user->update($validatedData);

            return $this->success('User updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500); // Internal Server Error
        }
    }



    public function deleteAccount()
    {
        $user = auth()->guard('user')->user();
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->delete();
        auth()->guard('user')->logout();
        return $this->success('User account deleted successfully');
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => new UserResource(auth()->guard('user')->user())
        ]);
    }
}
