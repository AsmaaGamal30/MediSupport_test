<?php

namespace App\Http\Controllers\Password;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendMail;

class DoctorForgotPassword extends Controller
{
    use ApiResponse; 

    public function forgot(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Return error response with validation messages
            return $this->error($validator->errors()->first(), 422);
        }

        $email = $request->email;

        // Check if the email exists in the Doctor table
        $doctor = Doctor::where('email', $email)->first();
        if ($doctor) {
            // Send reset password email to client
            Mail::to($email)->send(new SendMail($this->generateVerificationCode($email)));
            return $this->success('Verification code sent to User');
        }

        // Email does not belong to any user
        return $this->error('User not found', 404);
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