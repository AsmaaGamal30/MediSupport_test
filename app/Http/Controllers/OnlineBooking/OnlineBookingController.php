<?php

namespace App\Http\Controllers\OnlineBooking;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\OnlineBooking;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Notifications\DoctorBookingNotification;
use App\Http\Resources\OnlineBooking\OnlineBookingResource;
use App\Http\Requests\OnlineBookingRequests\OnlineDoctorRequest;
use App\Http\Requests\OnlineBookingRequests\DeleteBookingRequest;

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
        $booking->status = false;
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
            ->paginate(10);

        // Format the collection of bookings using OnlineBookingResource
        $formattedBookings = OnlineBookingResource::collection($bookings);

        $paginationData = [
            'first_page_url' => $bookings->url(1),
            'last_page_url' => $bookings->url($bookings->lastPage()),
            'prev_page_url' => $bookings->previousPageUrl(),
            'next_page_url' => $bookings->nextPageUrl(),
            'current_page' =>  $bookings->currentPage(),
            'last_page' => $bookings->lastPage(),
            'total' => $bookings->total(),

        ];

        return $this->successData('User bookings retrieved successfully', [
            'data' => $formattedBookings,
            'pagination' => $paginationData,
        ]);
    }

    public function deleteBooking(Request $request, $id)
    {
        // Check if the user is authenticated
        if (!Auth::guard('user')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        $userId = Auth::guard('user')->id();

        // Find the booking by ID
        $booking = OnlineBooking::where('user_id', $userId)
            ->where('id', $id)
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