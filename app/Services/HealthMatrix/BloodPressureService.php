<?php

namespace App\Services\HealthMatrix;

use App\Models\BloodPressure;
use App\Models\PressureAdvice;
use Illuminate\Support\Facades\Auth;

class BloodPressureService
{
    public function storeBloodPressure($validatedData)
    {
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
        return $bloodPressure;
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
        return Auth::user()->bloodPressures()->orderBy('created_at')->pluck('systolic', 'created_at');
    }

    public function getDiastolicData()
    {
        return Auth::user()->bloodPressures()->orderBy('created_at')->pluck('diastolic', 'created_at');
    }

    public function getLatestMeasurement()
    {
        return Auth::user()->bloodPressures()->latest()->first();
    }

    public function getLatestThreeMeasurements()
    {
        return Auth::user()->bloodPressures()->latest()->take(3)->get();
    }

    public function getAllMeasurements($perPage = 10)
    {
        return Auth::user()->bloodPressures()->paginate($perPage);
    }
}
