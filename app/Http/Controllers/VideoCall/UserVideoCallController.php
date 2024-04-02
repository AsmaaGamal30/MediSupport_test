<?php

namespace App\Http\Controllers\VideoCall;

use App\Models\VideoCall;
use Illuminate\Http\Request;
use App\Helpers\TwilioHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\VideoCall\CallRequest;
use App\Http\Requests\VideoCall\UserStartCallRequest;
use App\Traits\ApiResponse;

class UserVideoCallController extends Controller
{
    use ApiResponse; // Import the ApiResponse trait

    public function __construct()
    {
        $this->middleware('auth:user');
    }

    public function generateToken(Request $request)
    {
        $user = Auth::user();
        $identity = $user->name; // Or any other user identifier you want to use
        $token = TwilioHelper::generateToken($identity, 'user');

        return $this->sendData('Token generated successfully', ['generate_token' => $token]);
    }

    public function startCall(UserStartCallRequest $request)
    {
        // Check if the doctor_id is provided in the request
        if (!$request->has('doctor_id')) {
            return $this->error('Doctor ID is required', 400);
        }

        // Check if the worker is available
        $doctorId = $request->input('doctor_id');
        $pendingCall = VideoCall::where('doctor_id', $doctorId)
            ->where('status', 'accepted')
            ->whereNull('ended_at')
            ->first();

        if ($pendingCall) {
            return $this->error('Doctor is already in another call', 400);
        }

        // Create a new call with pending status
        $call = new VideoCall();
        $call->user_id = Auth::id();
        $call->doctor_id = $doctorId;
        $call->status = 'pending';
        $call->save();

        return $this->success('Call request sent to doctor');
    }

    public function endCall(CallRequest $request)
    {
        // Validate the request
        $validated = $request->validated();

        // Find the call
        $call = VideoCall::findOrFail($validated['call_id']);

        // Update the status to 'ended' and save the end time
        $call->status = 'ended';
        $call->ended_at = now(); // Save the current time as the end time
        $call->save();

        return $this->success('Call ended');
    }

    public function acceptCall(CallRequest $request)
    {
        // Validate incoming request
        $validated = $request->validated();

        // Get the authenticated user (user)
        $user = Auth::user();

        // Get the video call
        $videoCall = VideoCall::findOrFail($validated['call_id']);

        // Check if the user is assigned to this call
        if ($videoCall->user_id !== $user->id) {
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
}