<?php

namespace App\Http\Controllers\HealthMatrix;

use App\Http\Controllers\Controller;
use App\Http\Requests\HealthMatrixRequests\HeartRateRequest;
use App\Http\Resources\HeartRate\HeartRateAdviceResource;
use App\Http\Resources\HeartRate\HeartRateHistoryResource;
use App\Http\Resources\HeartRate\HeartRateResource;
use App\Http\Resources\HeartRate\LastHeartRateResource;
use App\Models\HeartRate;
use App\Models\HeartRateAdvice;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class HeartRateController extends Controller
{
    use ApiResponse;
    public function storeHeartRate(HeartRateRequest $request)
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $heartRate = $request->input('heart_rate');

            $advice = $this->determineHeartRateAdvice($heartRate);

            HeartRate::create([
                'heart_rate' => $heartRate,
                'user_id' => $userAuthId,
                'heart_rate_advice_id' => $advice->id
            ]);
            return $this->success('Heart rate store success', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to store heart rate', 500);
        }
    } //end storeHeartRate

    public function determineHeartRateAdvice($heartRate)
    {
        if ($heartRate < 60) {
            return HeartRateAdvice::where('key', 'low')->first();
        } elseif ($heartRate >= 60 && $heartRate <= 100) {
            return HeartRateAdvice::where('key', 'normal')->first();
        } elseif ($heartRate > 100) {
            return HeartRateAdvice::where('key', 'high')->first();
        }
    } // end determineHeartRateAdvice

    public function getAllHeartRateRecords()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $heartRateRecords = HeartRate::where('user_id', $userAuthId)
                ->orderByDesc('created_at')
                ->paginate(10);

            $data = HeartRateHistoryResource::collection($heartRateRecords);

            return $this->apiResponse(
                data: [
                    'current_page' => $heartRateRecords->currentPage(),
                    'Records' => $data,
                ],
                message: "All heart rate selected",
                statuscode: 200,
                error: false
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select all records', 500);
        }
    } // end getAllHeartRateRecords

    public function getLastSevenHeartRateRecords()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $heartRateRecords = HeartRate::where('user_id', $userAuthId)
                ->orderByDesc('created_at')
                ->take(7)
                ->get();

            $data = HeartRateHistoryResource::collection($heartRateRecords);

            return $this->apiResponse(
                data: $data,
                message: "Last seven heart rate records selected",
                statuscode: 200,
                error: false
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select last seven records', 500);
        }
    } // end selectLastSevenHeartRate

    public function getLastThreeHeartRateRecords()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $heartRateRecords = HeartRate::where('user_id', $userAuthId)
                ->orderByDesc('created_at')
                ->take(3)
                ->get();

            $data = HeartRateHistoryResource::collection($heartRateRecords);

            return $this->apiResponse(
                data: $data,
                message: "Last three heart rate records selected",
                statuscode: 200,
                error: false
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select last three records', 500);
        }
    } // end getLastThreeHeartRateRecords

    public function getLastHeartRateRecord()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $lastHeartRate = HeartRate::where('user_id', $userAuthId)
                ->latest()
                ->first();

            $data = new HeartRateResource($lastHeartRate);

            return $this->apiResponse(
                data: $data,
                message: "Last heart rate records selected",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select last record', 500);
        }
    } // getLastHeartRateRecord

    public function getUserRecommendedAdvice()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $latestHeartRate = HeartRate::where('user_id', $userAuthId)
                ->latest()
                ->first();

            if ($latestHeartRate) {
                $advice = $latestHeartRate->heartRateAdvice;

                return new HeartRateAdviceResource($advice);
            }
        } catch (\Exception $e) {
            return $this->error('Failed to select advice', 500);
        }
    } // getUserRecommendedAdvice


}
