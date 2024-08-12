<?php

namespace App\Services\OfflineBooking\Doctor;

use App\Http\Resources\OfflineBooking\AppointmentResource;
use App\Http\Resources\OfflineBooking\DoctorBookingResource;
use App\Models\Booking;
use App\Models\Date;
use App\Models\Doctor;
use App\Models\Time;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DoctorOfflineBookingService
{
    public function addAppointment($request)
    {
        $doctorId = Auth::guard('doctor')->id();

        $date = Date::where('doctor_id', $doctorId)
            ->where('date', $request->date)
            ->first();

        if (!$date) {
            $date = Date::create([
                'doctor_id' => $doctorId,
                'date' => $request->date,
            ]);
        }

        $existingTime = Time::where('doctor_id', $doctorId)
            ->where('date_id', $date->id)
            ->where('time', $request->time)
            ->exists();

        if ($existingTime) {
            return [
                'data' => ['message' => 'This time already exists for the selected date.'],
                'status' => 422
            ];
        }

        $selectedDate = Carbon::parse($date->date);
        $currentDate = Carbon::now();

        if ($selectedDate->lt($currentDate)) {
            return [
                'data' => ['message' => 'The selected date must be greater than or equal to the current date.'],
                'status' => 422
            ];
        }

        Time::create([
            'doctor_id' => $doctorId,
            'time' => $request->time,
            'date_id' => $date->id,
        ]);

        return [
            'data' => ['message' => 'Date and time stored successfully'],
            'status' => 200
        ];
    }

    public function getAllAppointments()
    {
        $doctorId = Auth::guard('doctor')->id();

        $appointments = Doctor::findOrFail($doctorId)
            ->dates()
            ->with('times')
            ->get();

        return [
            'data' => [
                'Appointments' => AppointmentResource::collection($appointments),
            ],
            'status' => 200
        ];
    }

    public function deleteAppointment($request)
    {
        $doctorId = Auth::guard('doctor')->id();

        $time = Time::where('id', $request->time_id)
            ->where('doctor_id', $doctorId)
            ->first();

        if (!$time) {
            return [
                'data' => ['message' => 'Time not found or you are not authorized to delete this time.'],
                'status' => 404
            ];
        }

        $date = Date::where('id', $request->date_id)
            ->where('doctor_id', $doctorId)
            ->first();

        if (!$date) {
            return [
                'data' => ['message' => 'Date not found or you are not authorized to delete this date.'],
                'status' => 404
            ];
        }

        $bookingsCount = Booking::where('date_id', $date->id)
            ->where('time_id', $time->id)
            ->count();

        if ($bookingsCount > 0) {
            return [
                'data' => ['message' => 'Cannot delete time with associated bookings.'],
                'status' => 403
            ];
        }

        $timesCount = Time::where('date_id', $date->id)->count();

        if ($timesCount > 1) {
            $time->delete();
        } else {
            $time->delete();
            $date->delete();
        }

        return [
            'data' => ['message' => 'Appointment deleted successfully'],
            'status' => 200
        ];
    }

    public function updateAppointment($request)
    {
        $doctorId = Auth::guard('doctor')->id();

        $time = Time::where('id', $request->time_id)
            ->where('doctor_id', $doctorId)
            ->first();

        if (!$time) {
            return [
                'data' => ['message' => 'Time not found or you are not authorized to update this appointment.'],
                'status' => 404
            ];
        }

        $date = Date::where('id', $request->date_id)
            ->where('doctor_id', $doctorId)
            ->first();

        if (!$date) {
            return [
                'data' => ['message' => 'Date not found or invalid date ID.'],
                'status' => 404
            ];
        }

        $otherTimesCount = Time::where('date_id', $date->id)
            ->where('id', '!=', $time->id)
            ->count();

        if ($otherTimesCount > 0) {
            if ($request->new_date != $date->date) {
                $existingDateWithTime = Date::where('date', $request->new_date)
                    ->where('id', $time->date_id)
                    ->first();

                if (!$existingDateWithTime) {
                    $newDate = Date::create([
                        'doctor_id' => $doctorId,
                        'date' => $request->new_date,
                    ]);

                    $time->date_id = $newDate->id;
                    $time->save();
                } else {
                    $time->time = $request->new_time;
                    $time->save();
                }
            } else {
                $time->time = $request->new_time;
                $time->save();
            }
        } else {
            $time->time = $request->new_time;
            $time->save();

            $date->date = $request->new_date;
            $date->save();
        }

        return [
            'data' => ['message' => 'Appointment updated successfully'],
            'status' => 200
        ];
    }

    public function getAllOfflineBookings()
    {
        $doctorId = Auth::guard('doctor')->id();

        $bookings = Booking::where('doctor_id', $doctorId)->paginate(10);

        $data = DoctorBookingResource::collection($bookings);

        return [
            'data' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'data' => $data,
            ],
            'status' => 200
        ];
    }
}
