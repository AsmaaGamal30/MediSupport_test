<?php

namespace App\Http\Controllers\HealthMatrix;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\HealthMatrixRequests\BloodPressureRequest;
use App\Http\Resources\BloodPressure\BloodPressureHistoryResource;
use App\Http\Resources\BloodPressure\BloodPressureResource;
use App\Models\BloodPressure;
use App\Models\PressureAdvice;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;


class BloodPressureController extends Controller
{
    use ApiResponse;
    public function store(BloodPressureRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $bloodPressure = new BloodPressure([
                'user_id' => Auth::user()->id,
                'systolic' => $validatedData['systolic'],
                'diastolic' => $validatedData['diastolic'],
            ]);

            $pressureAdviceKey = $this->determinePressureAdvice($bloodPressure);

            $bloodPressure->pressureAdvice()->associate(
                PressureAdvice::where('key', $pressureAdviceKey)->first()
            );

            $bloodPressure->save();

            return $this->success('Blood pressure record created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create blood pressure record', 500);
        }
    }

    private function determinePressureAdvice(BloodPressure $bloodPressure): string
    {
        $systolic = $bloodPressure->systolic;
        $diastolic = $bloodPressure->diastolic;

        if ($systolic <= 120 && $diastolic <= 80) {
            return 'normal';
        } elseif ($systolic >= 130 || $diastolic >= 80) {
            return 'high';
        } else {
            return 'low';
        }
    }


    public function getSystolicData()
    {
        try {
            $systolicData = Auth::user()->bloodPressures()->orderBy('created_at')->pluck('systolic', 'created_at');
            return $this->sendData('Systolic data retrieved successfully', $systolicData);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch systolic data', 500);
        }
    }

    public function getDiastolicData()
    {
        try {
            $diastolicData = Auth::user()->bloodPressures()->orderBy('created_at')->pluck('diastolic', 'created_at');
            return $this->sendData('Diastolic data retrieved successfully', $diastolicData);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch diastolic data', 500);
        }
    }

    public function getLatestMeasurement()
    {
        try {
            $latestMeasurement = Auth::user()->bloodPressures()->latest()->first();
            return new BloodPressureResource($latestMeasurement);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch the latest measurement', 500);
        }
    }

    public function getLatestThreeMeasurements()
    {
        try {
            $latestThreeMeasurements = Auth::user()->bloodPressures()->latest()->take(3)->get();
            return BloodPressureHistoryResource::collection($latestThreeMeasurements);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch the latest 3 measurements', 500);
        }
    }

    public function getAllMeasurements()
    {
        try {
            $user = Auth::user();
            $perPage = 5;
            $allMeasurements = $user->bloodPressures()->paginate($perPage);

            return BloodPressureHistoryResource::collection($allMeasurements);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch all measurements', 500);
        }
    }
}
