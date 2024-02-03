<?php

namespace App\Http\Controllers\HealthMatrix;

use App\Http\Controllers\Controller;
use App\Http\Requests\HealthMatrixRequests\BloodSugarRequest;
use App\Http\Resources\BloodSugar\BloodSugarAdviceResouce;
use App\Http\Resources\BloodSugar\BloodSugarHistoryResource;
use App\Http\Resources\BloodSugar\BloodSugarResource;
use App\Http\Resources\BloodSugar\BloodSugarStatusResource;
use App\Http\Resources\BloodSugar\LastBloodSugarResource;
use App\Models\BloodSugar;
use App\Models\BloodSugarAdvice;
use App\Models\BloodSugarStatus;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

use function Laravel\Prompts\error;

class BloodSugarController extends Controller
{
    use ApiResponse;

    public function storeBloodSugar(BloodSugarRequest $request)
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $bloodSugarLevel = $request->input('level');
            $statusId = $request->input('blood_sugar_statuses_id');

            $advice = $this->determineSugarAdvice($bloodSugarLevel, $statusId);

            BloodSugar::create([
                'level' => $bloodSugarLevel,
                'user_id' => $userAuthId,
                'blood_sugar_statuses_id' => $statusId,
                'blood_sugar_advice_id' => $advice->id
            ]);
            return $this->success('Blood sugar store success', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to store blood sugar', 500);
        }
    } //end storeBloodSugar

    public function determineSugarAdvice($bloodSugarLevel, $status)
    {
        if ($status == 1) {
            if ($bloodSugarLevel < 70) {
                return BloodSugarAdvice::where('key', 'low')->first();
            } elseif ($bloodSugarLevel >= 70 && $bloodSugarLevel <= 100) {
                return BloodSugarAdvice::where('key', 'normal')->first();
            } elseif ($bloodSugarLevel > 100) {
                return BloodSugarAdvice::where('key', 'high')->first();
            }
        }
    } // end determineSugarAdvice

    public function getAllBloodSugarRecords()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $bloodSugarRecords = BloodSugar::where('user_id', $userAuthId)
                ->orderByDesc('created_at')
                ->paginate(10);

            $data = BloodSugarHistoryResource::collection($bloodSugarRecords);

            return $this->apiResponse(
                data: [
                    'current_page' => $bloodSugarRecords->currentPage(),
                    'Records' => $data,
                ],
                message: "All blood sugar selected",
                statuscode: 200,
                error: false
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select all records', 500);
        }
    } // end getAllBloodSugarRecords

    public function getLastThreeBloodSugarRecords()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $bloodSugarRecords = BloodSugar::where('user_id', $userAuthId)
                ->orderByDesc('created_at')
                ->take(3)
                ->get();

            $data = BloodSugarHistoryResource::collection($bloodSugarRecords);

            return $this->apiResponse(
                data: $data,
                message: "Last three blood sugar records selected",
                statuscode: 200,
                error: false
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select last three records', 500);
        }
    } // end getLastThreeBloodSugarRecords

    public function getLastSevenBloodSugarRecords()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $bloodSugarRecords = BloodSugar::where('user_id', $userAuthId)
                ->orderByDesc('created_at')
                ->take(7)
                ->get();

            $data = BloodSugarResource::collection($bloodSugarRecords);

            return $this->apiResponse(
                data: $data,
                message: "Last seven blood sugar records selected",
                statuscode: 200,
                error: false
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select last seven records', 500);
        }
    } // end selectLastSevenBloodSugar

    public function getLastBloodSugarRecord()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $lastBloodSugar = BloodSugar::where('user_id', $userAuthId)
                ->latest()
                ->first();

            $data = new LastBloodSugarResource($lastBloodSugar);

            return $this->apiResponse(
                data: $data,
                message: "Last Blood Sugar Records Selected",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select last record', 500);
        }
    } // getLastBloodSugarRecord

    public function getUserRecommendedAdvice()
    {
        try {
            $userAuthId = auth()->guard('user')->user()->id;

            $latestBloodSugar = BloodSugar::where('user_id', $userAuthId)
                ->latest()
                ->first();

            if ($latestBloodSugar) {
                $advice = $latestBloodSugar->bloodSugarAdvice;

                return new BloodSugarAdviceResouce($advice);
            }
        } catch (\Exception $e) {
            return $this->error('Failed to select advice', 500);
        }
    } // getUserRecommendedAdvice

    public function getAllBloodSugarStatus()
    {
        try {
            $statuses = BloodSugarStatus::all();

            $data = BloodSugarStatusResource::collection($statuses);

            return $this->apiResponse(
                data: $data,
                message: "All blood sugar status selected",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select blood sugar status', 500);
        }
    }
}
