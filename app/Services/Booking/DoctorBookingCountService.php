<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\OnlineBooking;
use Carbon\Carbon;

class DoctorBookingCountService
{
    public function getBookingCounts($doctorId)
    {
        // Total bookings count
        $totalOnlineBookingsCount = OnlineBooking::where('doctor_id', $doctorId)->count();
        $totalOfflineBookingsCount = Booking::where('doctor_id', $doctorId)->count();
        $totalBookingsCount = $totalOnlineBookingsCount + $totalOfflineBookingsCount;

        // New bookings count (created today)
        $newOnlineBookingsCount = OnlineBooking::where('doctor_id', $doctorId)
            ->whereDate('created_at', '>=', Carbon::today())
            ->count();
        $newOfflineBookingsCount = Booking::where('doctor_id', $doctorId)
            ->whereDate('created_at', '>=', Carbon::today())
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

        return [
            'total_bookings_count' => $totalBookingsCount,
            'new_bookings_count' => $newBookingsCount,
            'old_bookings_count' => $oldBookingsCount,
        ];
    }
}
