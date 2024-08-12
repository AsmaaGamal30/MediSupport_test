<?php

namespace App\Services\Password;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;

class ResetPasswordService
{
    use ApiResponse;

    public function verifyVerificationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'verification_code' => 'required|numeric|digits:4',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $email = $request->email;
        $verificationCode = $request->verification_code;

        $user = User::where('email', $email)->first();
        if (!$user) {
            return $this->error('Email is invalid', 422);
        }

        if ($this->isValidVerificationCode($email, $verificationCode)) {
            return $this->success('Verification code is valid');
        }

        return $this->error('Invalid verification code', 422);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $email = $request->email;
        $password = $request->password;

        $user = User::where('email', $email)->first();
        if (!$user) {
            return $this->error('Email is invalid', 422);
        }

        $user->password = bcrypt($password);
        $user->save();

        return $this->success('Password reset successfully');
    }

    private function isValidVerificationCode($email, $verificationCode)
    {
        $storedVerificationCode = Cache::get('verification_code_' . $email);
        return $storedVerificationCode && $storedVerificationCode == $verificationCode;
    }
}
