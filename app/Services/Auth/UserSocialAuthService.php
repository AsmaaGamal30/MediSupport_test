<?php

namespace App\Services\Auth;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserSocialAuthService
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleProviderCallback($provider, $accessProviderToken)
    {
        try {
            $socialiteUser = Socialite::driver($provider)->stateless()->userFromToken($accessProviderToken);
        } catch (\Exception $e) {
            return ['error' => 'Invalid provider or token'];
        }

        $existingUser = User::where('provider_id', $socialiteUser->id)
            ->where('provider_name', $provider)
            ->first();

        $userWithEmail = User::where('email', $socialiteUser->email)
            ->first();

        if ($existingUser) {
            Auth::login($existingUser);
        } elseif ($userWithEmail) {
            return ['error' => 'Email is already associated with another account'];
        } else {
            $newUser = User::create([
                'email' => $socialiteUser->email,
                'name' => $provider == 'google' ? $socialiteUser->user['given_name'] : $socialiteUser->user['name'],
                'last_name' => $provider == 'google' ? $socialiteUser->user['family_name'] : '',
                'avatar' => $socialiteUser->avatar,
                'provider_id' => $socialiteUser->id,
                'provider_name' => $provider,
            ]);

            Auth::login($newUser);
        }

        $token = JWTAuth::fromUser(Auth::user());

        return [
            'token' => $token,
            'user' => Auth::user(),
        ];
    }
}
