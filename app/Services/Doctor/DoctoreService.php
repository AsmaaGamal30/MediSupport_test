<?php

namespace App\Services\Doctor;

use App\Models\Booking;
use App\Models\Doctor;
use App\Models\OnlineBooking;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DoctorService
{
    public function getDoctorsPaginated($perPage = 10)
    {
        return Doctor::query()->paginate($perPage);
    }

    public function deleteDoctor($id)
    {
        $doctor = Doctor::find($id);
        if ($doctor) {
            $doctor->delete();
            return true;
        }
        return false;
    }

    public function updatePassword($request)
    {
        $doctorId = Auth::guard('doctor')->id();
        $doctor = Doctor::find($doctorId);

        if (!Hash::check($request->current_password, $doctor->password)) {
            throw ValidationException::withMessages(['current_password' => ['Current password does not match']]);
        }

        $doctor->password = Hash::make($request->new_password);
        $doctor->save();

        return true;
    }

    public function getDoctorCount()
    {
        return Doctor::count();
    }

    public function getFirstEightDoctors()
    {
        return Doctor::take(8)->get();
    }

    public function updateDoctor($request, $id)
    {
        $doctor = Doctor::find($id);
        if ($doctor) {
            $doctor->update($request->all());
            return $doctor;
        }
        return null;
    }

    public function fetchLatestMedicalData($doctorId)
    {
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

        return $latestMedicalData;
    }

    public function fetchBloodPressureData($doctorId, $userId)
    {
        $isBooked = $this->isUserBooked($doctorId, $userId);
        if (!$isBooked) {
            return null;
        }
        $user = User::findOrFail($userId);
        return $user->bloodPressures()->paginate(10);
    }

    public function fetchBMIData($doctorId, $userId)
    {
        $isBooked = $this->isUserBooked($doctorId, $userId);
        if (!$isBooked) {
            return null;
        }
        $user = User::findOrFail($userId);
        return $user->BMIs()->paginate(10);
    }

    public function fetchBloodSugarData($doctorId, $userId)
    {
        $isBooked = $this->isUserBooked($doctorId, $userId);
        if (!$isBooked) {
            return null;
        }
        $user = User::findOrFail($userId);
        return $user->bloodSugars()->paginate(10);
    }

    private function isUserBooked($doctorId, $userId)
    {
        $isBooked = OnlineBooking::where('doctor_id', $doctorId)
            ->where('status', 2)
            ->where('user_id', $userId)
            ->exists();

        if (!$isBooked) {
            $isBooked = Booking::where('doctor_id', $doctorId)
                ->where('user_id', $userId)
                ->exists();
        }

        return $isBooked;
    }

    public function customPaginate($items, $perPage = 10)
    {
        $currentPage = request()->query('page', 1);
        $totalItems = count($items);
        $offset = ($currentPage - 1) * $perPage;
        $items = array_slice($items, $offset, $perPage);

        $paginator = new LengthAwarePaginator(
            $items,
            $totalItems,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginator;
    }
}
