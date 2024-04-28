<?php

namespace App\Http\Controllers\Doctors;

use App\Models\Doctor;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Doctor\DoctorResource;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\AuthRequests\UpdateDoctorRequest;
use App\Http\Requests\AuthRequests\UpdateDocrorsPasswordRequest;
use App\Models\BloodPressure;
use App\Models\BloodSugar;
use App\Models\BMI;
use App\Models\Booking;
use App\Models\HeartRate;
use App\Models\OnlineBooking;

class DoctorController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return DoctorResource::collection(Doctor::query()->paginate(10));
    }

    public function deleteDoctor($id)
    {
        if (!Auth::guard('admin')->check()) {
            return $this->error('Unauthorized', 401);
        }

        $doctor = Doctor::find($id);

        if (!$doctor) {
            return $this->error('Doctor not found', 404);
        }

        $doctor->delete();
        return $this->success('Doctor and their information deleted successfully');
    }

    public function updatePassword(UpdateDocrorsPasswordRequest $request)
    {
        if (!Auth::guard('doctor')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        try {
            $request->validate([
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $doctorId = Auth::guard('doctor')->id();
            $doctor = Doctor::find($doctorId);

            if (!Hash::check($request->current_password, $doctor->password)) {
                throw ValidationException::withMessages(['current_password' => ['Current password does not match']]);
            }

            $doctor->password = Hash::make($request->new_password);
            $doctor->save();

            return $this->success('Password updated successfully');
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422); // Pass the error message to the error method
        }
    }

    public function getDoctorCount()
    {
        // Check if the user is authenticated as an admin
        if (!Auth::guard('admin')->check()) {
            return $this->error('Unauthorized', 401);
        }

        // Retrieve the count of doctors from the database
        $doctorCount = Doctor::count();

        return $this->successData('Number of Doctors', ['count' => $doctorCount]);
    }

    public function getFirstEightDoctors()
    {
        // Check if the user is authenticated as an admin
        if (!Auth::guard('admin')->check()) {
            return $this->error('Unauthorized', 401);
        }

        // Retrieve the first 8 doctors from the database
        $doctors = Doctor::take(8)->get();

        // Return the doctor resource collection
        return DoctorResource::collection($doctors);
    }
    public function updateDoctor(UpdateDoctorRequest $request)
    {
        try {
            // Ensure the user is authenticated as a doctor
            $authenticatedDoctor = Auth::guard('doctor')->user();
            if (!$authenticatedDoctor) {
                return $this->error('Unauthenticated', 401);
            }
    
            // Retrieve the doctor ID from the request body
            $doctorId = $request->input('doctor_id');
    
            // Check if the authenticated doctor's ID matches the provided ID
            if ($authenticatedDoctor->id != $doctorId) {
                return $this->error('Unauthorized', 403);
            }
    
            // Find the doctor by ID
            $doctor = Doctor::find($doctorId);
    
            // Check if the doctor exists
            if (!$doctor) {
                return $this->error('Doctor not found', 404);
            }
    
            // Update the doctor's data based on the validated request
            $doctor->update($request->validated());
    
            // Return success response
            return $this->success('Doctor updated successfully');
        } catch (\Exception $e) {
            // Return error response if an exception occurs
            return $this->error($e->getMessage(), 500); // Internal Server Error
        }
    }
    

    public function fetchLatestMedicalData()
    {
        $doctorId = auth()->guard('doctor')->id();

        $onlineBookedUsers = OnlineBooking::where('doctor_id', $doctorId)
            ->where('status', 'accepted')
            ->pluck('user_id');
        $offlineBookedUsers = Booking::where('doctor_id', $doctorId)->pluck('user_id');

        $bookedUserIds = $onlineBookedUsers->merge($offlineBookedUsers)->unique();

        $latestMedicalData = [];

        foreach ($bookedUserIds as $userId) {
            $user = User::findOrFail($userId);

            $latestData = [
                'user_id' => $userId,
                'user_name' => $user->name . ' ' . $user->last_name,
                'email' => $user->email,
                'blood_sugar_level' => optional($user->bloodSugars->last())->level,
                'bmi_result' => optional($user->BMIs->last())->result,
                'heart_rate' => optional($user->heartRates->last())->heart_rate,
                'systolic' => optional($user->bloodPressures->last())->systolic,
                'diastolic' => optional($user->bloodPressures->last())->diastolic,
            ];
            $latestMedicalData[] = $latestData;
        }

        return $this->sendData('Latest medical data for all users who booked the doctor', $latestMedicalData);
    }

    public function fetchMedicalData($userId)
    {
        $doctorId = auth()->guard('doctor')->id();

        $isBooked = OnlineBooking::where('doctor_id', $doctorId)
            ->where('status', 'accepted')
            ->where('user_id', $userId)
            ->exists();

        if (!$isBooked) {
            $isBooked = Booking::where('doctor_id', $doctorId)
                ->where('user_id', $userId)
                ->exists();
        }

        if (!$isBooked) {
            return $this->error('User has not booked the doctor', 404);
        }

        $user = User::findOrFail($userId);

        $medicalData = [
            'user_id' => $userId,
            'user_name' => $user->name . ' ' . $user->last_name,
            'email' => $user->email,
            'blood_sugar_levels' => BloodSugar::where('user_id', $userId)->pluck('level')->toArray(),
            'bmi_results' => BMI::where('user_id', $userId)->pluck('result')->toArray(),
            'heart_rates' => HeartRate::where('user_id', $userId)->pluck('heart_rate')->toArray(),
            'systolic' => BloodPressure::where('user_id', $userId)->pluck('systolic')->toArray(),
            'diastolic' => BloodPressure::where('user_id', $userId)->pluck('diastolic')->toArray(),
        ];

        return $this->sendData('User medical data', $medicalData);
    }
}