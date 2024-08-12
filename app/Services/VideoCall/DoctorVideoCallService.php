<?php

namespace App\Services\VideoCall;

use App\Models\VideoCall;
use App\Models\OnlineBooking;
use App\Helpers\TwilioHelper;
use App\Notifications\UserBookingNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class DoctorVideoCallService
{
    public function generateToken($request)
    {
        $doctor = Auth::user();
        $bookingId = $request->input('booking_id');

        try {
            $booking = OnlineBooking::findOrFail($bookingId);

            if ($booking->status != 2) {
                return [
                    'data' => ['message' => 'Booking is not in the correct status.'],
                    'status' => 400
                ];
            }

            $videoCall = VideoCall::where('user_id', $booking->user_id)
                ->where('doctor_id', $booking->doctor_id)
                ->whereIn('status', ['pending', 'accepted'])
                ->first();

            if (!$videoCall) {
                $videoCall = VideoCall::create([
                    'user_id' => $booking->user_id,
                    'doctor_id' => $booking->doctor_id,
                    'status' => 'pending',
                ]);

                $roomName = 'room_' . uniqid();
                while (VideoCall::where('room_name', $roomName)->exists()) {
                    $roomName = 'room_' . uniqid();
                }

                $videoCall->room_name = $roomName;
                $videoCall->save();
            } elseif ($videoCall->status === 'ended' && $videoCall->user_id === $doctor->id) {
                $videoCall = VideoCall::create([
                    'user_id' => $booking->user_id,
                    'doctor_id' => $booking->doctor_id,
                    'status' => 'pending',
                ]);

                $roomName = 'room_' . uniqid();
                while (VideoCall::where('room_name', $roomName)->exists()) {
                    $roomName = 'room_' . uniqid();
                }

                $videoCall->room_name = $roomName;
                $videoCall->save();
            }

            $identity = $doctor->name;
            $roomName = $videoCall->room_name;
            $token = TwilioHelper::generateToken($identity, $roomName);

            $doctorName = $booking->doctor->first_name . ' ' . $booking->doctor->last_name;
            $userMessage = "Dr. $doctorName wants to call you.";
            $user = $booking->user;
            $notificationType = 'video_call';
            $onlineBookingId = $booking->id;

            Notification::send($user, new UserBookingNotification($userMessage, $notificationType, $onlineBookingId));

            return [
                'data' => [
                    'generate_token' => $token,
                    'room_name' => $roomName,
                    'call_id' => $videoCall->id,
                ],
                'status' => 200
            ];
        } catch (\Throwable $e) {
            throw new \Exception('OnlineBooking not found or invalid booking ID');
        }
    }

    public function startCall($request)
    {
        $callId = $request->input('call_id');

        $videoCall = VideoCall::find($callId);

        if (!$videoCall) {
            return [
                'data' => ['message' => 'VideoCall not found'],
                'status' => 404
            ];
        }

        if ($videoCall->status === 'accepted') {
            return [
                'data' => ['message' => 'This call has already been accepted.'],
                'status' => 400
            ];
        } elseif ($videoCall->status === 'ended') {
            return [
                'data' => ['message' => 'This call has already ended.'],
                'status' => 400
            ];
        }

        if ($videoCall->status === 'pending') {
            $videoCall->status = 'accepted';
            $videoCall->started_at = now();
            $videoCall->save();

            return [
                'data' => [
                    'room_name' => $videoCall->room_name,
                    'started_at' => $videoCall->started_at->format('Y-m-d H:i:s'),
                ],
                'status' => 200
            ];
        }

        return [
            'data' => ['message' => 'Invalid status for starting the call.'],
            'status' => 400
        ];
    }

    public function endCall($request)
    {
        $callId = $request->input('call_id');

        $videoCall = VideoCall::find($callId);

        if (!$videoCall) {
            return [
                'data' => ['message' => 'VideoCall not found'],
                'status' => 404
            ];
        }

        if ($videoCall->status === 'ended') {
            return [
                'data' => ['message' => 'This call has already ended.'],
                'status' => 400
            ];
        } elseif ($videoCall->status === 'pending') {
            return [
                'data' => ['message' => 'This call has not been accepted yet.'],
                'status' => 400
            ];
        }

        $videoCall->status = 'ended';
        $videoCall->ended_at = now();
        $videoCall->save();

        $booking = OnlineBooking::where('user_id', $videoCall->user_id)
            ->where('doctor_id', $videoCall->doctor_id)
            ->first();

        if ($booking) {
            $booking->status = 3;
            $booking->save();
        } else {
            return [
                'data' => ['message' => 'Booking not found for the ended video call'],
                'status' => 404
            ];
        }

        return [
            'data' => [
                'ended_at' => $videoCall->ended_at->format('Y-m-d H:i:s'),
                'room_name' => $videoCall->room_name,
            ],
            'status' => 200
        ];
    }
}
