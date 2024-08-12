<?php

namespace App\Http\Controllers\HealthMatrix;

use App\Models\BMI;
use App\Models\BMIAdvice;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\BMI\BMIResource;
use App\Http\Resources\BMI\BMIHistoryResource;
use App\Http\Requests\HealthMatrixRequests\BMICreateRequest;
use App\Services\HealthMatrix\BMIService;

class BMIController extends Controller
{
    use ApiResponse;

    protected $bmiService;

    public function __construct(BMIService $bmiService)
    {
        $this->bmiService = $bmiService;
    }

    public function store(BMICreateRequest $request)
    {
        if (!Auth::guard('user')->check()) {
            return $this->error('Unauthorized', 401);
        }

        $userId = Auth::guard('user')->user()->id;
        $validatedData = $request->validated();

        $bmi = $this->bmiService->calculateBMI($validatedData['height'], $validatedData['weight']);
        $result = $this->bmiService->getResult($validatedData['height'], $validatedData['weight']);
        $advice = $this->bmiService->getAdvice($result);

        $this->bmiService->storeBMIRecord($userId, $validatedData['gender'], $validatedData['age'], $validatedData['height'], $validatedData['weight'], $bmi, $advice);

        return $this->success('BMI data stored successfully');
    }

    public function getLastRecord()
    {
        if (!Auth::guard('user')->check()) {
            return $this->unauthorizedResponse();
        }

        $userId = Auth::guard('user')->user()->id;
        $lastRecord = $this->bmiService->getLastRecord($userId);
        $resource = new BMIResource($lastRecord);

        return $this->successData('Last BMI record retrieved successfully', $resource);
    }

    public function getAllRecords(Request $request)
    {
        if (!Auth::guard('user')->check()) {
            return $this->unauthorizedResponse();
        }

        $userId = Auth::guard('user')->user()->id;
        $records = $this->bmiService->getAllRecords($userId);
        $resource = BMIHistoryResource::collection($records['data']);

        $responseData = [
            'data' => $resource,
            'links' => $records['paginationLinks'],
        ];

        return $this->successData('BMI records retrieved successfully', $responseData);
    }

    public function getThreeLastRecords()
    {
        if (!Auth::guard('user')->check()) {
            return $this->unauthorizedResponse();
        }

        $userId = Auth::guard('user')->user()->id;
        $lastRecords = $this->bmiService->getThreeLastRecords($userId);
        $resource = BMIHistoryResource::collection($lastRecords);

        return $this->successData('Three most recent BMI records retrieved successfully', $resource);
    }

    public function getLastSevenBMIRecords()
    {
        if (!Auth::guard('user')->check()) {
            return $this->unauthorizedResponse();
        }

        $userId = Auth::guard('user')->user()->id;
        $bmiRecords = $this->bmiService->getLastSevenRecords($userId);
        $data = BMIResource::collection($bmiRecords);

        return $this->successData('Last seven BMI records selected', $data);
    }
}
