<?php

namespace App\Http\Controllers\VideoCall;

use App\Models\VideoCall;
use App\Models\OnlineBooking; 
use Illuminate\Http\Request;
use App\Helpers\TwilioHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\VideoCall\CallRequest;
use App\Http\Requests\VideoCall\DoctorStartCallRequest;
use App\Traits\ApiResponse;
use App\Notifications\UserBookingNotification;


class DoctorVideoCallController extends Controller
{
    use ApiResponse; 

    public function __construct()
    {
        $this->middleware('auth:doctor');
    }


    public function generateToken(Request $request)
    {
        $doctor = Auth::user();
        $bookingId = $request->input('booking_id');
        
        try {
            // Find the OnlineBooking record by ID
            $booking = OnlineBooking::findOrFail($bookingId);

            // Check if the booking status is correct
            if ($booking->status != 1) {
                return $this->error('Booking is not in the correct status.', 400);
            }

            // Find or create a video call for the user-doctor pair
            $videoCall = VideoCall::where('user_id', $booking->user_id)
                                  ->where('doctor_id', $booking->doctor_id)
                                  ->whereIn('status', ['pending', 'accepted'])
                                  ->first();

            if (!$videoCall) {
                // Create a new video call if none exists
                $videoCall = VideoCall::create([
                    'user_id' => $booking->user_id,
                    'doctor_id' => $booking->doctor_id,
                    'status' => 'pending',
                ]);

                // Generate a unique room name for the new video call
                $roomName = 'room_' . uniqid();
                while (VideoCall::where('room_name', $roomName)->exists()) {
                    $roomName = 'room_' . uniqid();
                }

                $videoCall->room_name = $roomName;
                $videoCall->save();
            } elseif ($videoCall->status === 'ended' && $videoCall->user_id === $doctor->id) {
                // Create a new video call if the existing one is ended
                $videoCall = VideoCall::create([
                    'user_id' => $booking->user_id,
                    'doctor_id' => $booking->doctor_id,
                    'status' => 'pending',
                ]);

                // Generate a unique room name for the new video call
                $roomName = 'room_' . uniqid();
                while (VideoCall::where('room_name', $roomName)->exists()) {
                    $roomName = 'room_' . uniqid();
                }

                $videoCall->room_name = $roomName;
                $videoCall->save();
            }

            // Generate token for the video call
            $identity = $doctor->name;
            $roomName = $videoCall->room_name;
            $token = TwilioHelper::generateToken($identity, $roomName);

            // Notify the user about the booking
            $doctorName = $booking->doctor->first_name . ' ' . $booking->doctor->last_name;
            $userMessage = "Dr. $doctorName wants to call you.";
            $user = $booking->user;
            $notificationType = 'video_call';
            $onlineBookingId = $booking->id;

            $user->notify(new UserBookingNotification($userMessage, $notificationType, $onlineBookingId));

            // Prepare the response data including the call_id
            $responseData = [
                'generate_token' => $token,
                'room_name' => $roomName,
                'call_id' => $videoCall->id,
            ];

            return $this->sendData('Token generated successfully', $responseData);
        } catch (\Throwable $th) {
            // Handle any exceptions (e.g., ModelNotFoundException)
            return $this->error('OnlineBooking not found or invalid booking ID', 404);
        }
    }
    


public function startCall(Request $request)
{
    $callId = $request->input('call_id');

    // Find the VideoCall record by call_id
    $videoCall = VideoCall::find($callId);

    if (!$videoCall) {
        return $this->error('VideoCall not found', 404);
    }

    // Check if the existing video call is already accepted or ended
    if ($videoCall->status === 'accepted') {
        return $this->error('This call has already been accepted.', 400);
    } elseif ($videoCall->status === 'ended') {
        return $this->error('This call has already ended.', 400);
    }

    // If status is 'pending', change it to 'accepted'
    if ($videoCall->status === 'pending') {
        // Update status to 'accepted'
        $videoCall->status = 'accepted';
        $videoCall->started_at = now(); // Record the current time as started_at
        $videoCall->save();

        // Prepare the response data
        $responseData = [
            'room_name' => $videoCall->room_name,
            'started_at' => $videoCall->started_at->format('Y-m-d H:i:s'), // Format date as needed
            // Include any other relevant data you want to return
        ];

        return $this->sendData('Call accepted successfully', $responseData);
    }

    // If none of the above conditions match, return an error
    return $this->error('Invalid status for starting the call.', 400);
}





public function endCall(Request $request)
{
    $callId = $request->input('call_id');

    // Find the VideoCall record by call_id
    $videoCall = VideoCall::find($callId);

    if (!$videoCall) {
        return $this->error('VideoCall not found', 404);
    }

    // Check if the existing video call is already ended or pending
    if ($videoCall->status === 'ended') {
        return $this->error('This call has already ended.', 400);
    } elseif ($videoCall->status === 'pending') {
        return $this->error('This call has not been accepted yet.', 400);
    }

    // Update status to 'ended' and record end time
    $videoCall->status = 'ended'; // Make sure 'ended' is a valid enum value
    $videoCall->ended_at = now(); // Record the current time as end time
    $videoCall->save();

    // Find the corresponding OnlineBooking and update its status
    $booking = OnlineBooking::where('user_id', $videoCall->user_id)
                            ->where('doctor_id', $videoCall->doctor_id)
                            ->first();

    if ($booking) {
        $booking->status = 3; // Update status to 3 (or whichever status code indicates ended)
        $booking->save();
    } else {
        // Handle case where booking is not found
        return $this->error('Booking not found for the ended video call', 404);
    }

    // Prepare the response data
    $responseData = [
        'ended_at' => $videoCall->ended_at->format('Y-m-d H:i:s'), // Format date as needed
        'room_name' => $videoCall->room_name,
        // Include any other relevant data you want to return
    ];

    // Return success response with data
    return $this->sendData('Call ended successfully', $responseData);
}

}
