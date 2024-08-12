<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfflineBooking\BookingCount;
use App\Services\Booking\DoctorBookingCountService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DoctorBookingCountController extends Controller
{
    use ApiResponse;

    protected $bookingCountService;

    public function __construct(DoctorBookingCountService $bookingCountService)
    {
        $this->bookingCountService = $bookingCountService;
    }

    public function getAllBookingCount()
    {
        try {
            $doctorId = auth()->guard('doctor')->id();

            // Get booking counts using the service
            $bookingCountsData = $this->bookingCountService->getBookingCounts($doctorId);

            // Instantiate the BookingCount resource with the data
            $bookingCountResource = new BookingCount($bookingCountsData);

            // Return the response
            return $this->apiResponse(
                data: $bookingCountResource,
                message: "Booking counts retrieved successfully",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            // Handle exception
            return $this->error('Failed to retrieve booking counts', 500);
        }
    }
}
