<?php

namespace App\Http\Controllers\HealthMatrix;

use App\Http\Controllers\Controller;
use App\Http\Requests\HealthMatrixRequests\BloodPressureRequest;
use App\Http\Resources\BloodPressure\BloodPressureHistoryResource;
use App\Http\Resources\BloodPressure\BloodPressureResource;
use App\Services\HealthMatrix\BloodPressureService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class BloodPressureController extends Controller
{
    use ApiResponse;

    protected $bloodPressureService;

    public function __construct(BloodPressureService $bloodPressureService)
    {
        $this->bloodPressureService = $bloodPressureService;
    }

    public function store(BloodPressureRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $this->bloodPressureService->storeBloodPressure($validatedData);

            return $this->success('Blood pressure record created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create blood pressure record', 500);
        }
    }

    public function getSystolicData()
    {
        try {
            $systolicData = $this->bloodPressureService->getSystolicData();
            return $this->sendData('Systolic data retrieved successfully', $systolicData);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch systolic data', 500);
        }
    }

    public function getDiastolicData()
    {
        try {
            $diastolicData = $this->bloodPressureService->getDiastolicData();
            return $this->sendData('Diastolic data retrieved successfully', $diastolicData);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch diastolic data', 500);
        }
    }

    public function getLatestMeasurement()
    {
        try {
            $latestMeasurement = $this->bloodPressureService->getLatestMeasurement();
            return new BloodPressureResource($latestMeasurement);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch the latest measurement', 500);
        }
    }

    public function getLatestThreeMeasurements()
    {
        try {
            $latestThreeMeasurements = $this->bloodPressureService->getLatestThreeMeasurements();
            return BloodPressureHistoryResource::collection($latestThreeMeasurements);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch the latest 3 measurements', 500);
        }
    }

    public function getAllMeasurements()
    {
        try {
            $perPage = 10;
            $allMeasurements = $this->bloodPressureService->getAllMeasurements($perPage);

            return BloodPressureHistoryResource::collection($allMeasurements);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch all measurements', 500);
        }
    }
}
