<?php

namespace App\Http\Controllers\Password;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Traits\ApiResponse; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class DoctorResetPassword extends Controller
{
    use ApiResponse;

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
            return $this->error($validator->errors()->first(), 422);
        }

        $email = $request->email;
        $verificationCode = $request->verification_code;

        // Check if the email exists in the Doctor table
        $doctor = Doctor::where('email', $email)->first();
        if (!$doctor) {
            // Email does not belong to any doctor
            return $this->error('Email is invalid', 422);
        }

        // Check if the verification code is valid
        if ($this->isValidVerificationCode($email, $verificationCode)) {
            return $this->success('Verification code is valid');
        }

        // Invalid verification code
        return $this->error('Invalid verification code', 422);
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
            return $this->error($validator->errors()->first(), 422);
        }

        $email = $request->email;
        $password = $request->password;

        // Check if the email exists in the Doctor table
        $doctor = Doctor::where('email', $email)->first();
        if (!$doctor) {
            // Doctor not found with the provided email
            return $this->error('Email is invalid', 422);
        }

        // Update the password in the Doctor table
        $doctor->password = bcrypt($password);
        $doctor->save();

        return $this->success('Password reset successfully');
    }

    private function isValidVerificationCode($email, $verificationCode)
    {
        // Retrieve the stored verification code from the cache
        $storedVerificationCode = Cache::get('verification_code_' . $email);

        // Check if the provided verification code matches the stored one
        return $storedVerificationCode && $storedVerificationCode == $verificationCode;
    }
}