<?php

namespace App\Services\OnlineBooking;

use App\Models\Doctor;
use App\Models\OnlineBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Notifications\DoctorBookingNotification;
use App\Http\Resources\OnlineBooking\OnlineBookingResource;
use App\Traits\ApiResponse;

class OnlineBookingService
{
    use ApiResponse;

    public function storeBooking($request, $user)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        // Ensure that the specified doctor exists and is active
        $doctor = Doctor::findOrFail($request->doctor_id);
        if (!$doctor->active_status) {
            return $this->error('The specified doctor is not active', 422);
        }

        // Check if the user has made a booking with this doctor within the last 6 hours
        $sixHoursAgo = Carbon::now()->subHours(6);
        $previousBooking = OnlineBooking::where('user_id', $user->id)
            ->where('doctor_id', $doctor->id)
            ->where('created_at', '>=', $sixHoursAgo)
            ->exists();

        if ($previousBooking) {
            return $this->error('You cannot make another booking for this doctor at the moment. Please try again later.', 423);
        }

        // Create a new booking
        $booking = new OnlineBooking();
        $booking->user_id = $user->id;
        $booking->doctor_id = $request->doctor_id;
        $booking->status = 0; // Assuming 0 means pending status
        $booking->save();

        // Notify the doctor about the new booking
        $userName = $user->name . ' ' . $user->last_name;
        $doctorMessage = "$userName is booking now.";
        $notificationType = 'booking_notification';
        $onlineBookingId = $booking->id;

        $doctor->notify(new DoctorBookingNotification($doctorMessage, $notificationType, $onlineBookingId));

        return $this->success('Booking request submitted successfully', 201);
    }

    public function getUserBookings($userId, $request)
    {
        $bookings = OnlineBooking::where('user_id', $userId)
            ->with('doctor')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Format the collection of bookings using OnlineBookingResource
        $formattedBookings = OnlineBookingResource::collection($bookings);

        $paginationData = [
            'first_page_url' => $bookings->url(1),
            'last_page_url' => $bookings->url($bookings->lastPage()),
            'prev_page_url' => $bookings->previousPageUrl(),
            'next_page_url' => $bookings->nextPageUrl(),
            'current_page' => $bookings->currentPage(),
            'last_page' => $bookings->lastPage(),
            'total' => $bookings->total(),
        ];

        return $this->successData('User bookings retrieved successfully', [
            'data' => $formattedBookings,
            'pagination' => $paginationData,
        ]);
    }

    public function deleteBooking($userId, $bookingId)
    {
        // Find the booking by ID
        $booking = OnlineBooking::where('user_id', $userId)
            ->where('id', $bookingId)
            ->first();

        // Check if the booking exists
        if (!$booking) {
            return $this->error('Booking not found', 404);
        }

        // Delete the booking
        $booking->delete();

        return $this->success('Booking deleted successfully');
    }
}