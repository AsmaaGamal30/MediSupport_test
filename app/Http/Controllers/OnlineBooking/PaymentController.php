<?php

namespace App\Http\Controllers\OnlineBooking;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\OnlineBooking\PaymentService;
use Exception;

class PaymentController extends Controller
{
    use ApiResponse;

    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function makePayment($bookingId, Request $request)
    {
        try {
            return $this->paymentService->makePayment($bookingId, $request);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
