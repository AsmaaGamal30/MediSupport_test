<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\UserSocialAuthRequest;
use App\Http\Resources\User\UserSocialAuthResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

use function Laravel\Prompts\error;

class UserSocialAuthController extends Controller
{
    use ApiResponse;

     // Redirect the user to the OAuth provider's authentication page.
     public function redirect($provider)
     {
       return Socialite::driver($provider)->stateless()->redirect();
     } //end redirect 

     // function to retrieve the user information and access token for test
    public function Callback($provider)
    {
        $user = Socialite::driver($provider)->stateless()->user();
        dd($user);
    } //end callback

    public function handleProviderCallback(UserSocialAuthRequest $request)
    {

        // Retrieve the provider and access token from the incoming request
        $provider = $request->input('provider');
        $accessProviderToken = $request->input('access_provider_token');

        // retrieve user details from the social provider with the provided access token
        try {
            $socialiteUser = Socialite::driver($provider)->stateless()->userFromToken($accessProviderToken);
        } catch (\Exception $e) {
            return $this->error(
              message: 'Invalid provider or token',
            );
        }

        // Check if a user with the same provider ID and provider name already exists
        $existingUser = User::where('provider_id', $socialiteUser->id)
            ->where('provider_name', $provider)
            ->first();

        // Check if a user with the same email exists but with a different provider
        $userWithEmail = User::where('email', $socialiteUser->email)
            ->first();

        // Check if the user already exists, log in if found
        if ($existingUser) {
            Auth::login($existingUser);
        }
        // If a user with the same email exists with a different provider, return an error response
        elseif ($userWithEmail) {
            return $this->error(
                message: 'Email is already associated with another account',
            );
        }
        // If the user doesn't exist, create a new user and log in
        else {
            $newUser = User::create([
                'email' => $socialiteUser->email,
                'first_name' => $provider == 'google' ? $socialiteUser->user['given_name'] : $socialiteUser->user['name'],
                'last_name' => $provider == 'google' ? $socialiteUser->user['family_name'] : '',
                'avatar' => $socialiteUser->avatar,
                'provider_id' => $socialiteUser->id,
                'provider_name' => $provider,
            ]);

            Auth::login($newUser);
        }

        // Generate a JWT token for the authenticated user
        $token = JWTAuth::fromUser(Auth::user());

        $socialLoginResource = new UserSocialAuthResource(Auth::user());

        return $this->apiResponse(
            data: [
                'token' => $token,
                'user' => $socialLoginResource,
            ],
            message: "User Login Success",
            statuscode: 200,
            error: false
        );
    }// end handleProviderCallback
}
