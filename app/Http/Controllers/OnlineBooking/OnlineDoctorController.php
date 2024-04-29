<?php

namespace App\Http\Controllers\OnlineBooking;

use App\Models\Doctor;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\OnlineBooking;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Doctor\DoctorResource;
use App\Notifications\UserBookingNotification;
use App\Http\Requests\OnlineBookingRequests\OnlineDoctorRequest;
use App\Http\Requests\OnlineBookingRequests\AcceptBookingRequest;
use App\Http\Requests\OnlineBookingRequests\DeleteBookingRequest;

class OnlineDoctorController extends Controller
{
    use ApiResponse;

    public function getOnlineDoctors()
    {
        if (!Auth::guard('user')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        $onlineDoctors = Doctor::where('active_status', 1)
            ->with('rates')
            ->get();

        if ($onlineDoctors->isEmpty()) {
            return $this->success('There are no online doctors currently.');
        }

        $onlineDoctors = $onlineDoctors->sortByDesc(function ($doctor) {
            return $doctor->rates->isEmpty() ? 0 : $doctor->rates->avg('rate');
        });

        $doctorResources = DoctorResource::collection($onlineDoctors);

        return $this->successData('Online doctors retrieved successfully', $doctorResources);
    }

    public function getFirstTenOnlineDoctors()
    {
        if (!Auth::guard('user')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        $onlineDoctors = Doctor::where('active_status', 1)
            ->with('rates')
            ->get();

        if ($onlineDoctors->isEmpty()) {
            return $this->success('There are no online doctors currently.');
        }

        $onlineDoctors = $onlineDoctors->sortByDesc(function ($doctor) {
            return $doctor->rates->isEmpty() ? 0 : $doctor->rates->avg('rate');
        });

        $onlineDoctors = $onlineDoctors->take(10);

        $doctorResources = DoctorResource::collection($onlineDoctors);

        return $this->successData('First ten online doctors retrieved successfully', $doctorResources);
    }

    public function getOnlineDoctorById($doctorId)
    {
        if (!Auth::guard('user')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        $validator = Validator::make(['doctor_id' => $doctorId], [
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $onlineDoctor = Doctor::where('id', $doctorId)
            ->where('active_status', 1)
            ->first();

        if (!$onlineDoctor) {
            return $this->error('Doctor not found or not currently online.', 404);
        }

        return $this->successData('Online doctor retrieved successfully', new DoctorResource($onlineDoctor));
    }
    public function acceptBooking(AcceptBookingRequest  $request)
    {
        if (!Auth::guard('doctor')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        $authenticatedDoctorId = Auth::guard('doctor')->user()->id;
        Log::info('Authenticated doctor ID: ' . $authenticatedDoctorId);

        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:online_bookings,id',
            'status' => 'required|in:accepted',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 400);
        }

        $booking = OnlineBooking::findOrFail($request->booking_id);

        Log::info('Booking doctor ID: ' . $booking->doctor_id);

        if ($booking->doctor_id !== $authenticatedDoctorId) {
            return $this->error('You are not authorized to accept this booking', 403);
        }

        $booking->status = $request->status;
        $booking->save();

        $userMessage = "Your booking request have accepted.";
        $user = $booking->user;
        $user->notify(new UserBookingNotification($userMessage));

        return $this->success('Booking accepted successfully', 200);
    }

    public function getDoctorBookings(Request $request)
    {
        if (!Auth::guard('doctor')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        $doctorId = Auth::guard('doctor')->id();

        $bookings = OnlineBooking::where('doctor_id', $doctorId)
            ->with('user')
            ->paginate(10);


        $formattedBookings = $bookings->map(function ($booking) {
            return [
                'username' => $booking->user->name . ' ' . $booking->user->last_name,
                'doctor_name' => $booking->doctor->first_name . ' ' . $booking->doctor->last_name,
                'status' => $booking->status,
            ];
        });

        $paginationData = [
            'first_page_url' => $bookings->url(1),
            'last_page_url' => $bookings->url($bookings->lastPage()),
            'prev_page_url' => $bookings->previousPageUrl(),
            'next_page_url' => $bookings->nextPageUrl(),
            'per_page' => $bookings->perPage(),
            'current_page' =>  $bookings->currentPage(),
            'last_page' =>  $bookings->lastPage(),
            'total' => $bookings->total(),
        ];

        return $this->successData('Doctor bookings retrieved successfully', [
            'data' => $formattedBookings,
            'pagination' => $paginationData,
        ]);
    }


    public function deleteBooking(Request  $request, $id)
    {
        // Check if the user is authenticated
        if (!Auth::guard('doctor')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        $doctorId = Auth::guard('doctor')->id();

        // Find the booking by ID
        $booking = OnlineBooking::where('doctor_id', $doctorId)
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