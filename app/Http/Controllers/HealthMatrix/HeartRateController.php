<?php

namespace App\Http\Controllers\HealthMatrix;

use App\Http\Controllers\Controller;
use App\Http\Requests\HealthMatrixRequests\HeartRateRequest;
use App\Http\Resources\HeartRate\HeartRateAdviceResource;
use App\Http\Resources\HeartRate\HeartRateHistoryResource;
use App\Http\Resources\HeartRate\HeartRateResource;
use App\Models\HeartRate;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\HealthMatrix\HeartRateService;

class HeartRateController extends Controller
{
    use ApiResponse;

    protected $heartRateService;

    public function __construct(HeartRateService $heartRateService)
    {
        $this->heartRateService = $heartRateService;
    }

    public function storeHeartRate(HeartRateRequest $request)
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $heartRate = $request->input('heart_rate');
            $advice = $this->heartRateService->determineHeartRateAdvice($heartRate);

            $this->heartRateService->storeHeartRate($userAuthId, $heartRate, $advice);

            return $this->success('Heart rate stored successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to store heart rate', 500);
        }
    }

    public function getAllHeartRateRecords()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $heartRateRecords = $this->heartRateService->getAllHeartRateRecords($userAuthId);

            $data = HeartRateResource::collection($heartRateRecords['data']);

            return $this->apiResponse(
                data: [
                    'current_page' => $heartRateRecords['pagination']['current_page'],
                    'last_page'=> $heartRateRecords['pagination']['last_page'],
                    'Records' => $data,
                ],
                message: "All heart rate records selected",
                statuscode: 200,
                error: false
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select all records', 500);
        }
    }

    public function getLastSevenHeartRateRecords()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $heartRateRecords = $this->heartRateService->getLastSevenHeartRateRecords($userAuthId);

            $data = HeartRateResource::collection($heartRateRecords);

            return $this->apiResponse(
                data: $data,
                message: "Last seven heart rate records selected",
                statuscode: 200,
                error: false
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select last seven records', 500);
        }
    }

    public function getLastThreeHeartRateRecords()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $heartRateRecords = $this->heartRateService->getLastThreeHeartRateRecords($userAuthId);

            $data = HeartRateResource::collection($heartRateRecords);

            return $this->apiResponse(
                data: $data,
                message: "Last three heart rate records selected",
                statuscode: 200,
                error: false
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select last three records', 500);
        }
    }

    public function getLastHeartRateRecord()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $lastHeartRate = $this->heartRateService->getLastHeartRateRecord($userAuthId);

            $data = new HeartRateResource($lastHeartRate);

            return $this->apiResponse(
                data: $data,
                message: "Last heart rate record selected",
                statuscode: 200,
                error: false
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select last record', 500);
        }
    }

    public function getUserRecommendedAdvice()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $advice = $this->heartRateService->getUserRecommendedAdvice($userAuthId);

            return new HeartRateAdviceResource($advice);
        } catch (\Exception $e) {
            return $this->error('Failed to select advice', 500);
        }
    }
}