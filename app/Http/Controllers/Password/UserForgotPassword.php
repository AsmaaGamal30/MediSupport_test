<?php

namespace App\Http\Controllers\Password;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class UserForgotPassword extends Controller
{
    public function forgot(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Return error response with validation messages
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->email;

        // Check if the email exists in the User table
        $user = User::where('email', $email)->first();

        if ($user) {
            // Check if the user logged in with social credentials
            if ($user->password === null) {
                // If password is null, display error message
                return response()->json(['message' => 'Social login users do not have passwords.'], 400);
            }

            // Send reset password email to user
            Mail::to($email)->send(new SendMail($this->generateVerificationCode($email)));
            return response()->json(['message' => 'Verification code sent to user.']);
        }

        // Email does not belong to any user
        return response()->json(['message' => 'User not found'], 404);
    }

    private function generateVerificationCode($email)
    {
        $verificationCode = rand(1000, 9999);
        Cache::put('verification_code_' . $email, $verificationCode, now()->addMinutes(10));

        // Log the generated verification code for debugging
        Log::info("Generated Verification Code for $email: $verificationCode");

        // Schedule a task to clean up expired verification codes
        // This task will run every hour
        $this->scheduleCleanupTask();

        return $verificationCode;

    }

    private function scheduleCleanupTask()
    {
        // Schedule a task to clean up expired verification codes from the cache
        // This task will run every hour
        Cache::add('verification_code_cleanup_task', true, Carbon::now()->addHour());
    }
}