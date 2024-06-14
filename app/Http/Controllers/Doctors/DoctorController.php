<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Resources\BloodSugar\BloodSugarResource;
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
use App\Http\Resources\BloodPressure\BloodPressureHistoryResource;
use App\Http\Resources\BMI\BMIHistoryResource;
use App\Models\BloodPressure;
use App\Models\BloodSugar;
use App\Models\BMI;
use App\Models\Booking;
use App\Models\HeartRate;
use App\Models\OnlineBooking;
use Illuminate\Pagination\Paginator;

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

    public function customPaginate($items, $perPage = 10)
    {
        $currentPage = request()->query('page', 1);
        $totalItems = count($items);
        $offset = ($currentPage - 1) * $perPage;
        $items = array_slice($items, $offset, $perPage);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $totalItems,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginator;
    }



    public function fetchLatestMedicalData()
    {
        $doctorId = auth()->guard('doctor')->id();

        $onlineBookedUsers = OnlineBooking::where('doctor_id', $doctorId)
            ->where('status', 2)
            ->pluck('user_id');
        $offlineBookedUsers = Booking::where('doctor_id', $doctorId)->pluck('user_id');

        $bookedUserIds = $onlineBookedUsers->merge($offlineBookedUsers)->unique();

        $latestMedicalData = [];

        foreach ($bookedUserIds as $userId) {
            $user = User::findOrFail($userId);

            $latestData = [
                'user_id' => $userId,
                'first_name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'blood_sugar_level' => optional($user->bloodSugars->last())->level,
                'bmi_result' => optional($user->BMIs->last())->result,
                'heart_rate' => optional($user->heartRates->last())->heart_rate,
                'systolic' => optional($user->bloodPressures->last())->systolic,
                'diastolic' => optional($user->bloodPressures->last())->diastolic,
                'heart_disease_prediction' => optional($user->predictions->last())->prediction,
            ];
            $latestMedicalData[] = $latestData;
        }

        // Paginate the latestMedicalData with a limit of 10
        $latestMedicalDataPaginated = $this->customPaginate($latestMedicalData, 10);

        return $this->sendData('Latest medical data for all users who booked the doctor', $latestMedicalDataPaginated);
    }


    public function fetchBloodPressureData($userId)
    {
        $doctorId = auth()->guard('doctor')->id();

        $isBooked = OnlineBooking::where('doctor_id', $doctorId)
            ->where('status', 2)
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
        $allMeasurements = $user->bloodPressures()->paginate(10);
        return BloodPressureHistoryResource::collection($allMeasurements);
    }

    public function fetchBMIData($userId)
    {
        $doctorId = auth()->guard('doctor')->id();

        $isBooked = OnlineBooking::where('doctor_id', $doctorId)
            ->where('status', 2)
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
        $allMeasurements = $user->BMIs()->paginate(10);
        return BMIHistoryResource::collection($allMeasurements);
    }

    public function fetchBloodSugerData($userId)
    {
        $doctorId = auth()->guard('doctor')->id();

        $isBooked = OnlineBooking::where('doctor_id', $doctorId)
            ->where('status', 2)
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
        $allMeasurements = $user->bloodSugars()->paginate(10);
        return BloodSugarResource::collection($allMeasurements);
    }
}
