<?php

namespace App\Http\Controllers\OnlineBooking;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\OnlineBooking;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Notifications\DoctorBookingNotification;
use App\Http\Requests\OnlineBookingRequests\OnlineDoctorRequest;

class OnlineBookingController extends Controller
{
    use ApiResponse;

    public function store(OnlineDoctorRequest $request)
    {
        // Check if the user is authenticated
        $user = Auth::guard('user')->user();

        if (!$user) {
            return $this->error('Unauthenticated', 401);
        }

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $userId = $user->id;
        $booking = new OnlineBooking();
        $booking->user_id = $userId;
        $booking->doctor_id = $request->doctor_id;
        $booking->status = 'waiting';
        $booking->save();

        $doctorMessage = "You have a new booking request.";
        $doctor = $booking->doctor;
        $doctor->notify(new DoctorBookingNotification($doctorMessage));

        return $this->success('Booking request submitted successfully', 201);
    }

    public function getUserBookings(Request $request)
    {
        // Check if the user is authenticated
        if (!Auth::guard('user')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        $userId = Auth::guard('user')->id();

        $bookings = OnlineBooking::where('user_id', $userId)
            ->with('doctor')
            ->get();

        $formattedBookings = $bookings->map(function ($booking) {
            return [
                'username' => $booking->user->first_name . ' ' . $booking->user->last_name,
                'doctor_name' => $booking->doctor->first_name . ' ' . $booking->doctor->last_name,
                'status' => $booking->status,
            ];
        });

        return $this->successData('User bookings retrieved successfully', $formattedBookings);
    }
}