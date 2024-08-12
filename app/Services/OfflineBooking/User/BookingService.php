<?php

namespace App\Services\OfflineBooking\User;

use App\Http\Resources\OfflineBooking\DoctorDetailsResource;
use App\Http\Resources\OfflineBooking\TimeResource;
use App\Http\Resources\OfflineBooking\UserBookingResource;
use App\Models\Booking;
use App\Models\Date;
use App\Models\Doctor;
use App\Models\Time;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingService
{
    public function getDoctorDetails($request)
    {
        $doctor = Doctor::findOrFail($request->id);
        $data = new DoctorDetailsResource($doctor);

        return [
            'data' => $data,
            'status' => 200
        ];
    }

    public function getDoctorDateTimes($request)
    {
        $dateId = $request->id;

        $times = Time::where('date_id', $dateId)
            ->doesntHave('booking')
            ->get();

        $data = TimeResource::collection($times);

        return [
            'data' => $data,
            'status' => 200
        ];
    }

    public function bookAppointment($request)
    {
        $userAuthId = Auth::guard('user')->id();

        $currentDate = now()->toDateString();
        $isDateValid = Date::where('id', $request->date_id)
            ->whereDate('date', '>=', $currentDate)
            ->exists();

        if (!$isDateValid) {
            return [
                'data' => ['message' => 'The specified date is not valid'],
                'status' => 400
            ];
        }

        $isDateAddedByDoctor = Date::where('id', $request->date_id)
            ->where('doctor_id', $request->doctor_id)
            ->exists();

        if (!$isDateAddedByDoctor) {
            return [
                'data' => ['message' => 'The specified doctor did not add this date'],
                'status' => 401
            ];
        }

        $isTimeAddedByDoctor = Time::where('id', $request->time_id)
            ->where('doctor_id', $request->doctor_id)
            ->exists();

        if (!$isTimeAddedByDoctor) {
            return [
                'data' => ['message' => 'The specified doctor did not add this time'],
                'status' => 402
            ];
        }

        $isTimeAssociatedWithDate = Time::where('id', $request->time_id)
            ->where('date_id', $request->date_id)
            ->exists();

        if (!$isTimeAssociatedWithDate) {
            return [
                'data' => ['message' => 'The specified time is not associated with the selected date'],
                'status' => 403
            ];
        }

        $isUserAlreadyBooked = Booking::where('time_id', $request->time_id)
            ->where('date_id', $request->date_id)
            ->where('user_id', $userAuthId)
            ->exists();

        if ($isUserAlreadyBooked) {
            return [
                'data' => ['message' => 'You have already booked this appointment'],
                'status' => 404
            ];
        }

        $isAppointmentAvailable = Time::where('id', $request->time_id)
            ->doesntHave('booking')
            ->exists();

        if (!$isAppointmentAvailable) {
            return [
                'data' => ['message' => 'The selected appointment is already booked'],
                'status' => 405
            ];
        }

        $hasPendingAppointment = Booking::where('doctor_id', $request->doctor_id)
            ->where('user_id', $userAuthId)
            ->whereHas('date', function ($query) {
                $query->whereDate('date', '>=', Carbon::today());
            })
            ->exists();

        if ($hasPendingAppointment) {
            return [
                'data' => ['message' => 'You already have a pending appointment with this doctor.'],
                'status' => 420
            ];
        }

        Booking::create([
            'doctor_id' => $request->doctor_id,
            'user_id' => $userAuthId,
            'time_id' => $request->time_id,
            'date_id' => $request->date_id,
        ]);

        return [
            'data' => ['message' => 'Appointment booked successfully'],
            'status' => 200
        ];
    }

    public function selectUserBooking()
    {
        $userId = Auth::guard('user')->id();

        $bookings = Booking::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $data = UserBookingResource::collection($bookings);

        return [
            'data' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'data' => $data,
            ],
            'status' => 200
        ];
    }

    public function deleteBooking($request)
    {
        $userId = Auth::guard('user')->id();

        $booking = Booking::findOrFail($request->id);

        if ($booking->user_id !== $userId) {
            return [
                'data' => ['message' => 'You are not authorized to delete this booking'],
                'status' => 403
            ];
        }

        $booking->delete();
        return [
            'data' => ['message' => 'Booking deleted successfully'],
            'status' => 200
        ];
    }
}
