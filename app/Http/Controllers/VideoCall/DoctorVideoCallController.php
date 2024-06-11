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
        $booking = OnlineBooking::findOrFail($bookingId);

        if ($booking->status != 2) {
            return $this->error('Booking is not in the correct status.', 400);
        }

        $videoCall = VideoCall::firstOrCreate(
            ['user_id' => $booking->user_id, 'doctor_id' => $booking->doctor_id],
            ['status' => 'pending']
        );

        if (!$videoCall->room_name) {
            // Generate a unique room name if not already set
            do {
                $roomName = 'room_' . uniqid();
            } while (VideoCall::where('room_name', $roomName)->exists());

            $videoCall->room_name = $roomName;
            $videoCall->save();
        }

        $identity = $doctor->name;
        $roomName = $videoCall->room_name;
        $token = TwilioHelper::generateToken($identity, $roomName);

        return $this->sendData('Token generated successfully', 
        ['generate_token' => $token, 'room_name' => $roomName]);
    }


    public function startCall(DoctorStartCallRequest $request)
    {
        // Check if the user_id is provided in the request
        if (!$request->has('user_id')) {
            return $this->error('User ID is required', 400);
        }

        // Create a new call with pending status
        $call = new VideoCall();
        $call->user_id = $request->user_id;
        $call->doctor_id = Auth::id();
        $call->status = 'pending';
        $call->save();

        return $this->success('Call request sent to user');
    }

    public function acceptCall(CallRequest $request)
    {
        // Validate incoming request
        $this->validate($request, [
            'call_id' => 'required|exists:video_calls,id',
        ]);

        // Get the authenticated user (doctor)
        $doctor = Auth::user();

        // Get the video call
        $videoCall = VideoCall::findOrFail($request->input('call_id'));

        // Check if the doctor is assigned to this call
        if ($videoCall->doctor_id !== $doctor->id) {
            return $this->error('You are not assigned to this call.', 400);
        }

        // Check if the call has already been accepted
        if ($videoCall->status === 'ended') {
            return $this->error('This call has already ended.', 400);
        }

        // Check if the call has already been accepted
        if ($videoCall->status === 'accepted') {
            return $this->error('This call has already been accepted.', 400);
        }

        // Update the status of the call to "accepted"
        $videoCall->status = 'accepted';
        $videoCall->started_at = now(); // Save the current time as the start time
        $videoCall->save();


        return $this->success('Call accepted successfully');
    }

    public function endCall(CallRequest $request)
    {
        // Validate the request
        $request->validate([
            'call_id' => 'required|exists:video_calls,id',
        ]);

        // Find the call
        $call = VideoCall::findOrFail($request->call_id);

        // Update the status to 'ended' and save the end time
        $call->status = 'ended';
        $call->ended_at = now(); // Save the current time as the end time
        $call->save();

        // Additional logic if needed

        return $this->success('Call ended');
    }
}