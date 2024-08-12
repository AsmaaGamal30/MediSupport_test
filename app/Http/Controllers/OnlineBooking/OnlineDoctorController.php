<?php

namespace App\Http\Controllers\OnlineBooking;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\OnlineBookingRequests\AcceptBookingRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\OnlineBooking\OnlineDoctorService;

class OnlineDoctorController extends Controller
{
    use ApiResponse;

    protected $onlineDoctorService;

    public function __construct(OnlineDoctorService $onlineDoctorService)
    {
        $this->onlineDoctorService = $onlineDoctorService;
    }

    public function getOnlineDoctors(Request $request)
    {
        // Check if the user is authenticated
        if (!Auth::guard('user')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        // Call the service method to get online doctors
        return $this->onlineDoctorService->getOnlineDoctors($request);
    }

    public function getFirstTenOnlineDoctors()
    {
        // Check if the user is authenticated
        if (!Auth::guard('user')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        // Call the service method to get the first ten online doctors
        return $this->onlineDoctorService->getFirstTenOnlineDoctors();
    }

    public function getOnlineDoctorById($doctorId)
    {
        // Check if the user is authenticated
        if (!Auth::guard('user')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        // Call the service method to get a specific online doctor by ID
        return $this->onlineDoctorService->getOnlineDoctorById($doctorId);
    }

    public function acceptBooking(AcceptBookingRequest $request)
    {
        // Check if the doctor is authenticated
        if (!Auth::guard('doctor')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        // Call the service method to accept a booking
        return $this->onlineDoctorService->acceptBooking($request);
    }

    public function getDoctorBookings(Request $request)
    {
        // Check if the doctor is authenticated
        if (!Auth::guard('doctor')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        // Call the service method to get doctor bookings
        return $this->onlineDoctorService->getDoctorBookings($request);
    }
}
