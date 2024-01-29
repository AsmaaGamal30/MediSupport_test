<?php

namespace App\Http\Controllers\Password;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class UserResetPassword extends Controller
{
    public function verifyVerificationCode(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'verification_code' => 'required|numeric|digits:4',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Return error response with validation messages
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->email;
        $verificationCode = $request->verification_code;
        // Check if the email exists in the Worker table
    $user = User::where('email', $email)->first();
    if (!$user) {
        // Email does not belong to any user
        return response()->json(['message' => 'Email is invalid'], 422);
    }

        // Check if the verification code is valid
        if ($this->isValidVerificationCode($email, $verificationCode)) {
            return response()->json(['message' => 'Verification code is valid']);
        }

        // Invalid verification code
        return response()->json(['message' => 'Invalid verification code'], 422);
    }

    public function resetPassword(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Return error response with validation messages
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->email;
        $password = $request->password;

        // Check if the email exists in the Client table
        $user = User::where('email', $email)->first();
        if (!$user) {
            // User not found with the provided email
            return response()->json(['message' => 'Email is invalid'], 422);
        }

        // Update the password in the Client table
        $user->password = bcrypt($password);
        $user->save();

        return response()->json(['message' => 'Password reset successfully']);
    }

    private function isValidVerificationCode($email, $verificationCode)
    {
        // Retrieve the stored verification code from the cache
        $storedVerificationCode = Cache::get('verification_code_' . $email);

        // Check if the provided verification code matches the stored one
        return $storedVerificationCode && $storedVerificationCode == $verificationCode;
    }
}