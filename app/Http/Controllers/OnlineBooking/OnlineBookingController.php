<?php

namespace App\Http\Controllers\OnlineBooking;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\OnlineBooking\OnlineBookingResource;
use App\Services\OnlineBooking\OnlineBookingService;

class OnlineBookingController extends Controller
{
    use ApiResponse;

    protected $onlineBookingService;

    public function __construct(OnlineBookingService $onlineBookingService)
    {
        $this->onlineBookingService = $onlineBookingService;
    }

    public function store(Request $request)
    {
        // Check if the user is authenticated
        $user = Auth::guard('user')->user();
        if (!$user) {
            return $this->error('Unauthenticated', 401);
        }

        // Call the service method to store the booking
        return $this->onlineBookingService->storeBooking($request, $user);
    }

    public function getUserBookings(Request $request)
    {
        // Check if the user is authenticated
        $user = Auth::guard('user')->user();
        if (!$user) {
            return $this->error('Unauthenticated', 401);
        }

        // Call the service method to get user bookings
        return $this->onlineBookingService->getUserBookings($user->id, $request);
    }

    public function deleteBooking(Request $request, $id)
    {
        // Check if the user is authenticated
        $user = Auth::guard('user')->user();
        if (!$user) {
            return $this->error('Unauthenticated', 401);
        }

        // Call the service method to delete the booking
        return $this->onlineBookingService->deleteBooking($user->id, $id);
    }
}
