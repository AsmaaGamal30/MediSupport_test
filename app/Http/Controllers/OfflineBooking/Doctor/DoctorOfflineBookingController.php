<?php

namespace App\Http\Controllers\OfflineBooking\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfflineBookingRequests\{DeleteAppointmentRequest, StoreDateRequest, StoreTimeRequest, UpdateAppointmentRequest};
use App\Http\Resources\OfflineBooking\AppointmentResource;
use App\Http\Resources\OfflineBooking\DoctorBookingResource;
use App\Models\Booking;
use App\Models\Date;
use App\Models\Doctor;
use App\Models\Time;
use App\Traits\ApiResponse;
use Carbon\Carbon;


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
    } //end storeDate

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

            // Check if the date associated with date_id is greater than or equal to the current date
            $selectedDate = Carbon::parse($date->date);
            $currentDate = Carbon::now();

            if ($selectedDate->lt($currentDate)) {
                return $this->error('The selected date must be greater than or equal to the current date.', 422);
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
    } //end storeTime

    public function getALLAppointment()
    {
        try {
            $doctorId = auth()->guard('doctor')->id();

            $appointments = Doctor::findOrFail($doctorId)
            ->dates()
            ->with('times')
            ->get();

            return $this->apiResponse(
                data: [
                    'Appointments' => AppointmentResource::collection($appointments),
                ],
                message: "All Appointments retrieved successfully",
                statuscode: 200,
                error: false,
            );
            
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve dates and times', 500);
        }
    }// end getALLAppointment
  
    public function deleteAppointment(DeleteAppointmentRequest $request)
    {
        try {
            $doctorId = auth()->guard('doctor')->id();
    
            $time = Time::where('id', $request->time_id)
                ->where('doctor_id', $doctorId)
                ->first();
    
            if (!$time) {
                return $this->error('Time not found or you are not authorized to delete this time.', 404);
            }
    
            $date = Date::where('id', $request->date_id)
                ->where('doctor_id', $doctorId)
                ->first();
    
            if (!$date) {
                return $this->error('Date not found or you are not authorized to delete this date.', 404);
            }
    
            $timesCount = Time::where('date_id', $date->id)->count();
    
            if ($timesCount > 1) {
                $time->delete();
            } else {
                $date->delete(); 
            }
    
            return $this->success('Appointment deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to delete appointment', 500);
        }
    }//end deleteAppointment
    
    public function updateAppointment(UpdateAppointmentRequest $request)
    {
        try {
            $doctorId = auth()->guard('doctor')->id();

            // Find the specific time to update based on time_id and doctor_id
            $time = Time::where('id', $request->time_id)
                ->where('doctor_id', $doctorId)
                ->first();

            if (!$time) {
                return $this->error('Time not found or you are not authorized to update this appointment.', 404);
            }

            // Find the date associated with the specified date_id
            $date = Date::where('id', $request->date_id)
                ->where('doctor_id', $doctorId)
                ->first();

            if (!$date) {
                return $this->error('Date not found or invalid date ID.', 404);
            }


            // Check if the date has multiple times associated with it
            $otherTimesCount = Time::where('date_id', $date->id)
                ->where('id', '!=', $time->id)
                ->count();

            if ($otherTimesCount > 0) {
                // Check if the new_date is different from the existing date
                if ($request->new_date != $date->date) {
                    // Check if the new_date exists with the specified time
                    $existingDateWithTime = Date::where('date', $request->new_date)
                        ->where('id', $time->date_id) // Check if this date is associated with the same time
                        ->first();

                    if (!$existingDateWithTime) {
                        // Create a new date for the specific time
                        $newDate = Date::create([
                            'doctor_id' => $doctorId,
                            'date' => $request->new_date,
                        ]);

                        // Associate the specific time with the new date
                        $time->date_id = $newDate->id;
                        $time->save();
                    } else {
                        // Update the time if the new_date is associated with the same time
                        $time->time = $request->new_time;
                        $time->save();
                    }
                } else {
                    // Update the time for the existing date if the new_date is the same
                    $time->time = $request->new_time;
                    $time->save();
                }
            } else {
                    $time->time = $request->new_time;
                    $time->save();

                    $date->date = $request->new_date;
                    $date->save();
            }
            return $this->success('Appointment updated successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to update appointment', 500);
        }
    }//end updateAppointment

    public function getAllOfflineBooking()
    {
        try {
            $doctorId = auth()->guard('doctor')->id();

            $bookings = Booking::where('doctor_id', $doctorId)->paginate(10);

            $data = DoctorBookingResource::collection($bookings);

            return $this->apiResponse(
                data: [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'data' => $data,
                ],
                message: "Doctor booking details retrieved successfully",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve doctor bookings', 500);
        }
    } //end getAllOfflineBooking


}
