<?php

namespace App\Http\Controllers\OfflineBooking\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfflineBookingRequests\BookingIdRequest;
use App\Http\Requests\OfflineBookingRequests\BookingRequest;
use App\Http\Requests\OfflineBookingRequests\DateIdRequest;
use App\Http\Requests\OfflineBookingRequests\DoctorIdRequest;
use App\Http\Resources\OfflineBooking\DoctorDetailsResource;
use App\Http\Resources\OfflineBooking\TimeResource;
use App\Http\Resources\OfflineBooking\UserBookingResource;
use App\Models\Booking;
use App\Models\Date;
use App\Models\Doctor;
use App\Models\Time;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponse;

    public function getDoctorDetails(DoctorIdRequest $request)
    {
        try {
            $doctor = Doctor::findorFail($request->id);
            $data = new DoctorDetailsResource($doctor);

            return $this->apiResponse(
                data: $data,
                message: "Doctor details retrieved successfully",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve doctor details', 500);
        }
    } //end getDoctorDetails

    public function getDoctorDateTimes(DateIdRequest $request)
    {

        try {
            $dateId = $request->id;

            $times = Time::where('date_id', $dateId)
                ->doesntHave('booking')
                ->get();

            $data = TimeResource::collection($times);

            return $this->apiResponse(
                data: $data,
                message: "Available times retrieved successfully",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve available times', 500);
        }
    } //end getDoctorDateTimes

    public function BookAppointment(BookingRequest $request)
    {
        try {
        $userAuthId = auth()->guard('user')->user()->id;
      
        $currentDate = now()->toDateString();
        $isDateValid = Date::where('id', $request->date_id)
            ->whereDate('date', '>=', $currentDate)
            ->exists();

        if (!$isDateValid) {
            return $this->error('The specified date is not valid', 400);
        }
        $isDateAddedByDoctor = Date::where('id', $request->date_id)
            ->where('doctor_id', $request->doctor_id)
            ->exists();

        if (!$isDateAddedByDoctor) {
            return $this->error('The specified doctor did not add this date', 400);
        }

        $isDateAddedByDoctor = Time::where('id', $request->time_id)
            ->where('doctor_id', $request->doctor_id)
            ->exists();

        if (!$isDateAddedByDoctor) {
            return $this->error('The specified doctor did not add this time', 400);
        }
        $isTimeAssociatedWithDate = Time::where('id', $request->time_id)
            ->where('date_id', $request->date_id)
            ->exists();

        if (!$isTimeAssociatedWithDate) {
            return $this->error('The specified time is not associated with the selected date', 400);
        }

        $isUserAlreadyBooked = Booking::where('time_id', $request->time_id)
            ->where('date_id', $request->date_id)
            ->where('user_id', $userAuthId)
            ->exists();

        if ($isUserAlreadyBooked) {
            return $this->error('You have already booked this appointment', 400);
        }

        $isAppointmentAvailable = Time::where('id', $request->time_id)
            ->doesntHave('booking')
            ->exists();

        if (!$isAppointmentAvailable) {
            return $this->error('The selected appointment is already booked', 400);
        }
        $hasPendingAppointment = Booking::where('doctor_id', $request->doctor_id)
            ->where('user_id', $userAuthId)
            ->whereHas('date', function ($query) {
                $query->whereDate('date', '>=', Carbon::today()); // Filter based on 'date' relationship
            })
            ->exists();


        if ($hasPendingAppointment) {
            return $this->error('You already have a pending appointment with this doctor.', 400);
        }

        Booking::create([
            'doctor_id' => $request->doctor_id,
            'user_id' => $userAuthId,
            'time_id' => $request->time_id,
            'date_id' => $request->date_id,
        ]);

        return $this->success('Appointment booked successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to book appointment', 500);
        }
    } //end BookAppointment

    public function selectUserBooking()
    {
        try {
            $userId = auth()->guard('user')->user()->id;

            $bookings = Booking::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $data = UserBookingResource::collection($bookings);

            return $this->apiResponse(
                data: [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'data' => $data,
                ],
                message: "User Booking details retrieved successfully",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve user bookings', 500);
        }
    } //end selectUserBooking


    public function deleteBooking(BookingIdRequest $request)
    {
        try {
            $userId = auth()->guard('user')->user()->id;

            $booking = Booking::findOrFail($request->id);

            if ($booking->user_id !==  $userId) {
                return $this->error('You are not authorized to delete this booking', 403);
            }

            $booking->delete();
            return $this->success('Booking deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to delete booking', 500);
        }
    } //end deleteBooking


}
