<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\UserSocialAuthRequest;
use App\Http\Resources\User\UserSocialAuthResource;
use App\Services\Auth\UserSocialAuthService;
use App\Traits\ApiResponse;

class UserSocialAuthController extends Controller
{
    use ApiResponse;

    protected $userSocialAuthService;

    public function __construct(UserSocialAuthService $userSocialAuthService)
    {
        $this->userSocialAuthService = $userSocialAuthService;
    }

    public function redirect($provider)
    {
        return $this->userSocialAuthService->redirectToProvider($provider);
    }

    public function handleProviderCallback(UserSocialAuthRequest $request)
    {
        $provider = $request->input('provider');
        $accessProviderToken = $request->input('access_provider_token');

        $result = $this->userSocialAuthService->handleProviderCallback($provider, $accessProviderToken);

        if (isset($result['error'])) {
            return $this->error($result['error']);
        }

        $socialLoginResource = new UserSocialAuthResource($result['user']);

        return $this->apiResponse(
            data: [
                'token' => $result['token'],
                'user' => $socialLoginResource,
            ],
            message: "User Login Success",
            statuscode: 200,
            error: false
        );
    }
}
