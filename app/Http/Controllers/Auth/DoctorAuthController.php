<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\DoctorLoginRequest;
use App\Http\Requests\AuthRequests\DoctorRegisterRequest;
use App\Http\Resources\Doctor\DoctorResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Traits\ApiResponse;
use Validator;

class DoctorAuthController extends Controller
{
    use ApiResponse;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:doctor', ['except' => ['login', 'register']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(DoctorLoginRequest $request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        if (!$token = auth()->guard('doctor')->attempt($request->validated())) {
            return $this->error('Invalid email or password', 401);
        }
        auth()->guard('doctor')->user()->update(['active_status' => 1]);
        return $this->createNewToken($token);
    }
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(DoctorRegisterRequest $request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return $this->error($validator->errors()->toJson(), 400);
        }
        $avatarUrl =  $request->file('avatar')->storeAs('avatar',  $request->file('avatar')->getClientOriginalName(), 'public');

        $doctor = Doctor::create(array_merge(
            $request->validated(),
            [
                'password' => bcrypt($request->password),
                'avatar' => $avatarUrl
            ]
        ));
        $avatarUrl = asset('storage' . $avatarUrl);

        return $this->success('Doctor successfully registered', 201);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $doctor = auth()->guard('doctor')->user();
        if ($doctor) {
            $doctor->update(['active_status' => 0]);
        }
        auth()->guard('doctor')->logout();
        return $this->success('Doctor successfully signed out');
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->guard('doctor')->refresh());
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return $this->sendData('', new DoctorResource(auth()->guard('doctor')->user()));
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
            'doctor' => new DoctorResource(auth()->guard('doctor')->user()),
        ]);
    }
}
