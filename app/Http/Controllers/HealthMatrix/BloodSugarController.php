<?php

namespace App\Http\Controllers\HealthMatrix;

use App\Http\Controllers\Controller;
use App\Http\Requests\HealthMatrixRequests\BloodSugarRequest;
use App\Http\Resources\BloodSugar\BloodSugarAdviceResouce;
use App\Http\Resources\BloodSugar\BloodSugarHistoryResource;
use App\Http\Resources\BloodSugar\BloodSugarResource;
use App\Http\Resources\BloodSugar\BloodSugarStatusResource;
use App\Models\BloodSugarStatus;
use App\Services\HealthMatrix\BloodSugarService;
use App\Traits\ApiResponse;

class BloodSugarController extends Controller
{
    use ApiResponse;

    protected $bloodSugarService;

    public function __construct(BloodSugarService $bloodSugarService)
    {
        $this->bloodSugarService = $bloodSugarService;
    }

    public function storeBloodSugar(BloodSugarRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $this->bloodSugarService->storeBloodSugar($validatedData);
            return $this->success('Blood sugar record created successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to store blood sugar', 500);
        }
    }

    public function getAllBloodSugarRecords()
    {
        try {
            $bloodSugarRecords = $this->bloodSugarService->getAllBloodSugarRecords();
            $data = BloodSugarResource::collection($bloodSugarRecords);

            return $this->apiResponse(
                data: [
                    'current_page' => $bloodSugarRecords->currentPage(),
                    'last_page' => $bloodSugarRecords->lastPage(),
                    'Records' => $data,
                ],
                message: "All blood sugar records retrieved successfully",
                statuscode: 200,
                error: false
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve all records', 500);
        }
    }

    public function getLastThreeBloodSugarRecords()
    {
        try {
            $bloodSugarRecords = $this->bloodSugarService->getLastThreeBloodSugarRecords();
            $data = BloodSugarResource::collection($bloodSugarRecords);

            return $this->apiResponse(
                data: $data,
                message: "Last three blood sugar records retrieved successfully",
                statuscode: 200,
                error: false
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve last three records', 500);
        }
    }

    public function getLastSevenBloodSugarRecords()
    {
        try {
            $bloodSugarRecords = $this->bloodSugarService->getLastSevenBloodSugarRecords();
            $data = BloodSugarResource::collection($bloodSugarRecords);

            return $this->apiResponse(
                data: $data,
                message: "Last seven blood sugar records retrieved successfully",
                statuscode: 200,
                error: false
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve last seven records', 500);
        }
    }

    public function getLastBloodSugarRecord()
    {
        try {
            $lastBloodSugar = $this->bloodSugarService->getLastBloodSugarRecord();
            $data = new BloodSugarResource($lastBloodSugar);

            return $this->apiResponse(
                data: $data,
                message: "Last blood sugar record retrieved successfully",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve last record', 500);
        }
    }

    public function getUserRecommendedAdvice()
    {
        try {
            $advice = $this->bloodSugarService->getUserRecommendedAdvice();

            if ($advice) {
                return new BloodSugarAdviceResouce($advice);
            } else {
                return $this->error('No advice found', 404);
            }
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve advice', 500);
        }
    }

    public function getAllBloodSugarStatus()
    {
        try {
            $statuses = BloodSugarStatus::all();
            $data = BloodSugarStatusResource::collection($statuses);

            return $this->apiResponse(
                data: $data,
                message: "All blood sugar statuses retrieved successfully",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve statuses', 500);
        }
    }
}
