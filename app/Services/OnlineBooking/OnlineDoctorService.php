<?php

namespace App\Services\OnlineBooking;

use App\Models\Doctor;
use App\Models\OnlineBooking;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Doctor\DoctorResource;
use App\Http\Resources\OnlineBooking\OnlineDoctorResource;
use App\Http\Resources\OnlineBooking\DoctorBookingResource;
use App\Notifications\UserBookingNotification;
use App\Traits\ApiResponse;

class OnlineDoctorService
{
    use ApiResponse;

    public function getOnlineDoctors($request)
    {
        $onlineDoctors = Doctor::where('active_status', 1)
            ->with('rates')
            ->paginate(10);

        if ($onlineDoctors->isEmpty()) {
            return $this->success('There are no online doctors currently.');
        }

        $formattedOnlineDoctors = DoctorResource::collection($onlineDoctors);

        $paginationData = [
            'first_page_url' => $onlineDoctors->url(1),
            'last_page_url' => $onlineDoctors->url($onlineDoctors->lastPage()),
            'prev_page_url' => $onlineDoctors->previousPageUrl(),
            'next_page_url' => $onlineDoctors->nextPageUrl(),
            'current_page' =>  $onlineDoctors->currentPage(),
            'last_page' => $onlineDoctors->lastPage(),
            'total' => $onlineDoctors->total(),
        ];

        return $this->successData('Online doctors retrieved successfully', [
            'data' => $formattedOnlineDoctors,
            'pagination' => $paginationData,
        ]);
    }

    public function getFirstTenOnlineDoctors()
    {
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
        $validator = Validator::make(['doctor_id' => $doctorId], [
            'doctor_id' => 'exists:doctors,id',
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

        return $this->successData('Online doctor retrieved successfully', new OnlineDoctorResource($onlineDoctor));
    }

    public function acceptBooking($request)
    {
        $authenticatedDoctorId = Auth::guard('doctor')->user()->id;
        Log::info('Authenticated doctor ID: ' . $authenticatedDoctorId);

        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:online_bookings,id',
            'status' => 'required|integer|in:1',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 400);
        }

        $booking = OnlineBooking::findOrFail($request->booking_id);
        Log::info('Booking doctor ID: ' . $booking->doctor_id);

        if ($booking->doctor_id !== $authenticatedDoctorId) {
            return $this->error('You are not authorized to accept this booking', 403);
        }

        if ($booking->status === 1) {
            return $this->error('This booking has already been accepted', 400);
        }

        $booking->status = 1;
        $booking->save();

        $doctorName = $booking->doctor->first_name . ' ' . $booking->doctor->last_name;
        $userMessage = "Dr. $doctorName has accepted your booking. You can now call with him.";
        $user = $booking->user;
        $notificationType = 'booking_notification';
        $onlineBookingId = $booking->id;

        $user->notify(new UserBookingNotification($userMessage, $notificationType, $onlineBookingId));

        return $this->success('Booking accepted successfully', 200);
    }

    public function getDoctorBookings($request)
    {
        $doctorId = Auth::guard('doctor')->id();

        $bookings = OnlineBooking::where('doctor_id', $doctorId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $formattedBookings = DoctorBookingResource::collection($bookings);

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
}
