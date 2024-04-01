<?php

namespace App\Helpers;

use Firebase\JWT\JWT;

class TwilioHelper
{
    public static function generateToken($identity)
    {
        $twilioAccountSid = env('TWILIO_ACCOUNT_SID');
        $twilioApiKey = env('TWILIO_API_KEY');
        $twilioApiSecret = env('TWILIO_API_SECRET');

        $payload = [
            'iss' => $twilioAccountSid,
            'sub' => $twilioApiKey,
            'exp' => time() + 3600, // Token expiration time (1 hour from now)
            'identity' => $identity,
        ];

        return JWT::encode($payload, $twilioApiSecret, 'HS256');
    }
}
