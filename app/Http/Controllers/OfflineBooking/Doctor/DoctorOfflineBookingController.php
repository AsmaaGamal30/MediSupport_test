<?php

namespace App\Http\Controllers\OfflineBooking\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfflineBookingRequests\StoreDateRequest;
use App\Http\Requests\OfflineBookingRequests\StoreTimeRequest;
use App\Http\Resources\OfflineBooking\DoctorBookingResource;
use App\Models\Booking;
use App\Models\Date;
use App\Models\Time;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DoctorOfflineBookingController extends Controller
{
    use ApiResponse;
    public function storeDate(StoreDateRequest $request)
    {
        try {

            $doctorId = auth()->guard('doctor')->id();

            Date::create([
                'doctor_id' => $doctorId,
                'date' => $request->date,
            ]);

            return $this->success('Date stored successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to store date', 500);
        }
    }//end storeDate

    public function storeTime(StoreTimeRequest $request)
    {
        try {

            $doctorId = auth()->guard('doctor')->id();

            // Check if the authenticated doctor is the same as the one who created the date
            $date = Date::findOrFail($request->date_id);

            if ($doctorId !== $date->doctor_id) {
                return $this->error('You are not authorized to add time for this date.', 403);
            }

            // Check if the time already exists for the doctor and date
            $existingTime = Time::where('doctor_id', $doctorId)
                ->where('date_id', $request->date_id)
                ->where('time', $request->time)
                ->exists();

            if ($existingTime) {
                return $this->error('This time already exists for the selected date.', 422);
            }

            Time::create([
                'doctor_id' => $doctorId,
                'time' => $request->time,
                'date_id' => $request->date_id,
            ]);

            return $this->success('Time stored successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to store time', 500);
        }
    }//end storeTime

    public function getAllOfflineBooking()
    {
        try {
            $doctorId = auth()->guard('doctor')->id();

            $bookings = Booking::where('doctor_id', $doctorId)->paginate(10);

            $data = DoctorBookingResource::collection($bookings);

            return $this->apiResponse(
                data: [
                    'current_page' => $bookings->currentPage(),
                    'data' => $data,
                ],
                message: "Doctor booking details retrieved successfully",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve doctor bookings', 500);
        }
    }//end getAllOfflineBooking
}
