<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\UpdateDocrorsPasswordRequest;
use App\Http\Resources\BloodPressure\BloodPressureHistoryResource;
use App\Http\Resources\BMI\BMIHistoryResource;
use App\Http\Resources\BloodSugar\BloodSugarResource;
use App\Http\Resources\Doctor\DoctorResource;
use App\Services\Doctor\DoctorService;
use App\Traits\ApiResponse;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DoctorController extends Controller
{
    use ApiResponse;

    protected $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    public function index()
    {
        $doctors = $this->doctorService->getDoctorsPaginated();
        return DoctorResource::collection($doctors);
    }

    public function deleteDoctor($id)
    {
        if (!Auth::guard('admin')->check()) {
            return $this->error('Unauthorized', 401);
        }

        $deleted = $this->doctorService->deleteDoctor($id);

        if (!$deleted) {
            return $this->error('Doctor not found', 404);
        }

        return $this->success('Doctor and their information deleted successfully');
    }

    public function updatePassword(UpdateDocrorsPasswordRequest $request)
    {
        if (!Auth::guard('doctor')->check()) {
            return $this->error('Unauthenticated', 401);
        }

        try {
            $this->doctorService->updatePassword($request);
            return $this->success('Password updated successfully');
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function getDoctorCount()
    {
        if (!Auth::guard('admin')->check()) {
            return $this->error('Unauthorized', 401);
        }

        $doctorCount = $this->doctorService->getDoctorCount();
        return $this->successData('Number of Doctors', ['count' => $doctorCount]);
    }

    public function getFirstEightDoctors()
    {
        if (!Auth::guard('admin')->check()) {
            return $this->error('Unauthorized', 401);
        }

        $doctors = $this->doctorService->getFirstEightDoctors();
        return DoctorResource::collection($doctors);
    }

    public function updateDoctor(Request $request, $id)
    {
        try {
            $authenticatedDoctor = Auth::guard('doctor')->user();
            if (!$authenticatedDoctor) {
                return $this->error('Unauthenticated', 401);
            }

            if ($authenticatedDoctor->id != $id) {
                return $this->error('Unauthorized', 403);
            }

            $doctor = $this->doctorService->updateDoctor($request, $id);

            if (!$doctor) {
                return $this->error('Doctor not found', 404);
            }

            return $this->success('Doctor updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function fetchLatestMedicalData()
    {
        $doctorId = Auth::guard('doctor')->id();

        $latestMedicalData = $this->doctorService->fetchLatestMedicalData($doctorId);

        $latestMedicalDataPaginated = $this->doctorService->customPaginate($latestMedicalData, 10);

        return $this->sendData('Latest medical data for all users who booked the doctor', $latestMedicalDataPaginated);
    }

    public function fetchBloodPressureData($userId)
    {
        $doctorId = Auth::guard('doctor')->id();

        $data = $this->doctorService->fetchBloodPressureData($doctorId, $userId);

        if (!$data) {
            return $this->error('User has not booked the doctor', 404);
        }

        return BloodPressureHistoryResource::collection($data);
    }

    public function fetchBMIData($userId)
    {
        $doctorId = Auth::guard('doctor')->id();

        $data = $this->doctorService->fetchBMIData($doctorId, $userId);

        if (!$data) {
            return $this->error('User has not booked the doctor', 404);
        }

        return BMIHistoryResource::collection($data);
    }

    public function fetchBloodSugerData($userId)
    {
        $doctorId = Auth::guard('doctor')->id();

        $data = $this->doctorService->fetchBloodSugarData($doctorId, $userId);

        if (!$data) {
            return $this->error('User has not booked the doctor', 404);
        }

        return BloodSugarResource::collection($data);
    }
}
