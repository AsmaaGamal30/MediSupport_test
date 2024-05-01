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
use App\Http\Requests\OnlineBookingRequests\AcceptBookingRequest;
use App\Http\Resources\OnlineBooking\DoctorBookingResource;


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

    public function acceptBooking(AcceptBookingRequest $request)
{
    if (!Auth::guard('doctor')->check()) {
        return $this->error('Unauthenticated', 401);
    }

    $authenticatedDoctorId = Auth::guard('doctor')->user()->id;
    Log::info('Authenticated doctor ID: ' . $authenticatedDoctorId);

    // Validate request data
    $validator = Validator::make($request->all(), [
        'booking_id' => 'required|exists:online_bookings,id',
        'status' => 'required|boolean|in:1',
    ]);

    if ($validator->fails()) {
        return $this->error($validator->errors()->first(), 400);
    }

    // Ensure the authenticated doctor is authorized to accept the booking
    $booking = OnlineBooking::findOrFail($request->booking_id);
    if ($booking->doctor_id !== $authenticatedDoctorId) {
        return $this->error('You are not authorized to accept this booking', 403);
    }

    // Update booking status
    $booking->status = true;
    $booking->save();

    // Notify user about the booking acceptance
    $userMessage = "Your booking request has been accepted.";
    $user = $booking->user;
    $user->notify(new UserBookingNotification($userMessage));

    return $this->success('Booking accepted successfully', 200);
}public function getDoctorBookings(Request $request)
{
    if (!Auth::guard('doctor')->check()) {
        return $this->error('Unauthenticated', 401);
    }

    $doctorId = Auth::guard('doctor')->id();

    $bookings = OnlineBooking::where('doctor_id', $doctorId)
        ->with('user')
        ->paginate(10);

    // Format the collection of bookings using DoctorBookingResource
    $formattedBookings = DoctorBookingResource::collection($bookings);

    // Pagination data
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

    // Return success response with formatted bookings and pagination data
    return $this->successData('Doctor bookings retrieved successfully', [
        'data' => $formattedBookings,
        'pagination' => $paginationData,
    ]);
}
}