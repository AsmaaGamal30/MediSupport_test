<?php

namespace App\Http\Controllers\Password;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Password\ForgotPasswordService;
use App\Traits\ApiResponse;
use Exception;

class UserForgotPasswordController extends Controller
{
    use ApiResponse;

    protected $forgotPasswordService;

    public function __construct(ForgotPasswordService $forgotPasswordService)
    {
        $this->forgotPasswordService = $forgotPasswordService;
    }

    public function forgot(Request $request)
    {
        try {
            return $this->forgotPasswordService->handleForgotPassword($request);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
