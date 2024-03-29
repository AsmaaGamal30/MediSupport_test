<?php

namespace App\Http\Controllers\Auth;

use Validator;
use App\Models\admin;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Admin\AdminResource;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\AuthRequests\AdminLoginResquest;
use App\Http\Requests\AuthRequests\UpdateAdminRequest;

class AdminAuthController extends Controller
{
    use ApiResponse;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin', ['except' => ['login', 'register']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(AdminLoginResquest $request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }
        if (!$token = auth()->guard('admin')->attempt($request->validated())) {
            return $this->error('Invalid email or password', 401);
        }
        return $this->createNewToken($token);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->guard('admin')->logout();
        return $this->success('Admin successfully signed out');
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->guard('admin')->refresh());
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return $this->sendData('', new AdminResource(auth()->guard('admin')->user()));
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
            'expires_in' => auth()->factory()->getTTL() * 60,
            'admin' => auth()->guard('admin')->user()
        ]);
    }


    public function updatePassword(UpdateAdminRequest $request)
{
    // Check if the doctor is authenticated
    if (!Auth::guard('admin')->check()) {
        return response()->json(['error' => 'Unauthenticated'], 401);
    }

    try {
        // Validate the request data
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Retrieve the authenticated doctor's ID
        $adminId = Auth::guard('admin')->id();

        // Retrieve the authenticated doctor
        $admin = Admin::find($adminId);

        // Verify if the current password matches the one in the database
        if (!Hash::check($request->current_password, $admin->password)) {
            throw ValidationException::withMessages(['current_password' => ['Current password does not match']]);
        }

        // Update the doctor's password
        $admin->password = Hash::make($request->new_password);
        $admin->save();

        // Return success response
        return $this->success('Password updated successfully');
    } catch (ValidationException $e) {
        // Return validation error response as JSON
        return $this->error($e->getMessage(), 422); // Pass the error message to the error method
    }
}
}