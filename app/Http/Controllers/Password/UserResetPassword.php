<?php

namespace App\Http\Controllers\Password;

use App\Http\Controllers\Controller;
use App\Services\Password\ResetPasswordService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class UserResetPassword extends Controller
{
    use ApiResponse;

    protected $resetPasswordService;

    public function __construct(ResetPasswordService $resetPasswordService)
    {
        $this->resetPasswordService = $resetPasswordService;
    }

    public function verifyVerificationCode(Request $request)
    {
        try {
            return $this->resetPasswordService->verifyVerificationCode($request);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            return $this->resetPasswordService->resetPassword($request);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
