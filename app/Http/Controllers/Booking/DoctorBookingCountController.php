<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfflineBooking\BookingCount;
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
    
            // Total bookings count
            $totalOnlineBookingsCount = OnlineBooking::where('doctor_id', $doctorId)->count();
            $totalOfflineBookingsCount = Booking::where('doctor_id', $doctorId)->count();
            $totalBookingsCount = $totalOnlineBookingsCount + $totalOfflineBookingsCount;
    
            // New bookings count (created today)
            $newOnlineBookingsCount = OnlineBooking::where('doctor_id', $doctorId)
                ->whereDate('created_at','>=', Carbon::today())
                ->count();
            $newOfflineBookingsCount = Booking::where('doctor_id', $doctorId)
                ->whereDate('created_at','>=',Carbon::today())
                ->count();
            $newBookingsCount = $newOnlineBookingsCount + $newOfflineBookingsCount;
    
            // Old bookings count (created before today)
            $oldOnlineBookingsCount = OnlineBooking::where('doctor_id', $doctorId)
                ->whereDate('created_at', '<', Carbon::today())
                ->count();
            $oldOfflineBookingsCount = Booking::where('doctor_id', $doctorId)
                ->whereDate('created_at', '<', Carbon::today())
                ->count();
            $oldBookingsCount = $oldOnlineBookingsCount + $oldOfflineBookingsCount;
    
            // Prepare booking counts data
            $bookingCountsData = [
                'total_bookings_count' => $totalBookingsCount,
                'new_bookings_count' => $newBookingsCount,
                'old_bookings_count' => $oldBookingsCount,
            ];
    
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
