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
use App\Http\Resources\OnlineBooking\OnlineDoctorResource;
use App\Http\Resources\OnlineBooking\DoctorBookingResource;
use App\Http\Requests\OnlineBookingRequests\AcceptBookingRequest;


class OnlineDoctorController extends Controller
{
    use ApiResponse;

    public function getOnlineDoctors(Request $request)
    {
        // Check if the user is authenticated
        if (!Auth::guard('user')->check()) {
            return $this->error('Unauthenticated', 401);
        }


        $onlineDoctors = Doctor::where('active_status', 1)
            ->with('rates')
            ->paginate(10);

        // If there are no online doctors, return success response with a message
        if ($onlineDoctors->isEmpty()) {
            return $this->success('There are no online doctors currently.');
        }

        // Format the paginated collection of online doctors using DoctorResource
        $formattedOnlineDoctors = DoctorResource::collection($onlineDoctors);

        // Construct pagination data
        $paginationData = [
            'first_page_url' => $onlineDoctors->url(1),
            'last_page_url' => $onlineDoctors->url($onlineDoctors->lastPage()),
            'prev_page_url' => $onlineDoctors->previousPageUrl(),
            'next_page_url' => $onlineDoctors->nextPageUrl(),
            'current_page' =>  $onlineDoctors->currentPage(),
            'last_page' => $onlineDoctors->lastPage(),
            'total' => $onlineDoctors->total(),
        ];

        // Return success response with formatted data and pagination information
        return $this->successData('Online doctors retrieved successfully', [
            'data' => $formattedOnlineDoctors,
            'pagination' => $paginationData,
        ]);
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

    public function acceptBooking(AcceptBookingRequest $request)
    {
        // Check if doctor is authenticated
        if (!Auth::guard('doctor')->check()) {
            return $this->error('Unauthenticated', 401);
        }
    
        $authenticatedDoctorId = Auth::guard('doctor')->user()->id;
        Log::info('Authenticated doctor ID: ' . $authenticatedDoctorId);
    
        // Validate request data
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:online_bookings,id',
            'status' => 'required|integer|in:1',
        ]);
    
        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 400);
        }
    
        // Retrieve booking by ID
        $booking = OnlineBooking::findOrFail($request->booking_id);
        Log::info('Booking doctor ID: ' . $booking->doctor_id);
    
        // Ensure the authenticated doctor is authorized to accept the booking
        if ($booking->doctor_id !== $authenticatedDoctorId) {
            return $this->error('You are not authorized to accept this booking', 403);
        }
    
        // Check if the booking has already been accepted
        if ($booking->status === 1) {
            return $this->error('This booking has already been accepted', 400);
        }
    
        // Update booking status
        $booking->status = 1;
        $booking->save();
    
        // Get the doctor's name
        $doctorName = $booking->doctor->first_name . ' ' . $booking->doctor->last_name;
    
        // Notify user about the booking acceptance with doctor's name
        $userMessage = "Dr. $doctorName has accepted your booking. You can now call with him.";
        $user = $booking->user;
        $notificationType = 'booking_notification';
        $onlineBookingId = $booking->id;

    
        $user->notify(new UserBookingNotification($userMessage, $notificationType, $onlineBookingId));
    
        return $this->success('Booking accepted successfully', 200);
    }
    

    // public function completeBookingStatus(Request $request)
    // {
    //     if (!Auth::guard('user')->check()) {
    //         return $this->error('Unauthenticated', 401);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'booking_id' => 'required|exists:online_bookings,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->error($validator->errors()->first(), 400);
    //     }

    //     $booking = OnlineBooking::where('id', $request->booking_id)
    //         ->where('status', 1)
    //         ->where('user_id', Auth::guard('user')->id())
    //         ->first();

    //     if (!$booking) {
    //         return $this->error('Booking not found or not eligible for completion', 404);
    //     }

    //     $booking->status = 2;
    //     $booking->save();

    //     return $this->success('Booking status updated successfully', 200);
    // }

    public function getDoctorBookings(Request $request)
    {
        if (!Auth::guard('doctor')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        $doctorId = Auth::guard('doctor')->id();

        $bookings = OnlineBooking::where('doctor_id', $doctorId)
            ->with('user')
            ->orderBy('created_at', 'desc')
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