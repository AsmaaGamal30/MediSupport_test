<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\OnlineBooking;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DoctorBookingCountController extends Controller
{
    use ApiResponse;
    public function getAllBookingCount()
    {
        try {
            $doctorId = auth()->guard('doctor')->id();

            $onlineBookingsCount = OnlineBooking::where('doctor_id', $doctorId)->count();

            $offlineBookingsCount = Booking::where('doctor_id', $doctorId)->count();

            $totalBookingsCount = $onlineBookingsCount + $offlineBookingsCount;

            return $this->apiResponse(
                data: [
                    'total_bookings_count' => $totalBookingsCount
                ],
                message: "Total bookings count retrieved successfully",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error("Failed to retrieve bookings count", 500);
        }
    }


    public function getNewBookingCount()
    {
        try {

            $newOnlineBookingsCount = OnlineBooking::where('created_at', '>=', Carbon::today())
                ->count();


            $newOfflineBookingsCount = Booking::where('created_at', '>=', Carbon::today())
            ->count();

            $totalNewBookingsCount = $newOnlineBookingsCount + $newOfflineBookingsCount;
            return $this->apiResponse(
                data: [
                    'total_new_bookings_count' => $totalNewBookingsCount
                ],
                message: "New bookings count retrieved successfully",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve new bookings count', 500);
        }
    }

    public function getOldBookingCount()
    {
        try {

            $oldOnlineBookingsCount = OnlineBooking::where('created_at', '<', Carbon::today())
            ->count();


            $oldOfflineBookingsCount = Booking::where('created_at', '<', Carbon::today())
            ->count();

            $totalOldBookingsCount = $oldOnlineBookingsCount + $oldOfflineBookingsCount;
            return $this->apiResponse(
                data: [
                    'total_old_bookings_count' => $totalOldBookingsCount
                ],
                message: "old bookings count retrieved successfully",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve old bookings count', 500);
        }
    }
}
