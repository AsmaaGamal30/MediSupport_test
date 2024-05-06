<?php

namespace App\Http\Resources\OfflineBooking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingCount extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalBookings = $this['total_bookings_count'];
        $newBookings = $this['new_bookings_count'];
        $oldBookings = $this['old_bookings_count'];

        $percentageNewBookings = $totalBookings > 0 ? intval(($newBookings / $totalBookings) * 100) : 0;
        $percentageOldBookings = $totalBookings > 0 ? intval(($oldBookings / $totalBookings) * 100) : 0;

        return [
            'total_bookings_count' => $totalBookings,
            'new_bookings_count' => $newBookings,
            'old_bookings_count' => $oldBookings,
            'percentage_new_bookings' => $percentageNewBookings.'%',
            'percentage_old_bookings' => $percentageOldBookings.'%',
        ];
    }
}
