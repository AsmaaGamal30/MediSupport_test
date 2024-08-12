<?php

namespace App\Services\Password;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;

class ForgotPasswordService
{
    use ApiResponse;

    public function handleForgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if ($user) {
            if ($user->password === null) {
                return $this->error('Social login users do not have passwords.', 400);
            }

            Mail::to($email)->send(new SendMail($this->generateVerificationCode($email)));
            return $this->success('Verification code sent to user.');
        }

        return $this->error('User not found', 404);
    }

    private function generateVerificationCode($email)
    {
        $verificationCode = rand(1000, 9999);
        Cache::put('verification_code_' . $email, $verificationCode, now()->addMinutes(10));

        Log::info("Generated Verification Code for $email: $verificationCode");

        $this->scheduleCleanupTask();

        return $verificationCode;
    }

    private function scheduleCleanupTask()
    {
        Cache::add('verification_code_cleanup_task', true, Carbon::now()->addHour());
    }
}
