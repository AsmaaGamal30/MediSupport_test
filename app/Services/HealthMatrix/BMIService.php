<?php

namespace App\Services\HealthMatrix;

use App\Models\BMI;
use App\Models\BMIAdvice;

class BMIService
{
    public function calculateBMI($height, $weight)
    {
        $heightInMeters = $height / 100; // Assuming height is in centimeters
        $bmi = $weight / ($heightInMeters * $heightInMeters);
        return round($bmi, 2);
    }

    public function getResult($height, $weight)
    {
        $bmi = $this->calculateBMI($height, $weight);

        if ($bmi < 18.5) {
            return 'Underweight';
        } elseif ($bmi >= 18.5 && $bmi <= 24.9) {
            return 'Normal weight';
        } elseif ($bmi >= 25 && $bmi <= 29.9) {
            return 'Overweight';
        } elseif ($bmi >= 30 && $bmi <= 34.9) {
            return 'Obesity (Class 1)';
        } elseif ($bmi >= 35 && $bmi <= 39.9) {
            return 'Obesity (Class 2)';
        } else {
            return 'Extreme Obesity (Class 3)';
        }
    }

    public function getAdvice($result)
    {
        $advice = BMIAdvice::where('key', str_replace(' ', '_', strtolower($result)))->first();

        if (!$advice) {
            $defaultAdvice = BMIAdvice::where('key', 'default')->first();

            if (!$defaultAdvice) {
                $defaultAdvice = BMIAdvice::create([
                    'key' => 'default',
                    'advice' => 'Default advice message',
                ]);
            }

            $advice = $defaultAdvice;
        }

        return $advice;
    }

    public function storeBMIRecord($userId, $gender, $age, $height, $weight, $bmi, $advice)
    {
        $bmiRecord = new BMI();
        $bmiRecord->user_id = $userId;
        $bmiRecord->gender = $gender;
        $bmiRecord->age = $age;
        $bmiRecord->height = $height;
        $bmiRecord->weight = $weight;
        $bmiRecord->result = $bmi;

        if ($advice !== null) {
            $bmiRecord->bmi_advice_id = $advice->id;
        } else {
            $bmiRecord->bmi_advice_id = null;
        }

        $bmiRecord->save();
    }

    public function getLastRecord($userId)
    {
        return BMI::where('user_id', $userId)->latest()->first();
    }

    public function getAllRecords($userId)
    {
        $records = BMI::where('user_id', $userId)->paginate(10);
        $paginationLinks = [
            'first_page_url' => $records->url(1),
            'last_page_url' => $records->url($records->lastPage()),
            'prev_page_url' => $records->previousPageUrl(),
            'next_page_url' => $records->nextPageUrl(),
            'current_page' => $records->currentPage(),
            'last_page' => $records->lastPage(),
            'total' => $records->total(),
        ];

        return ['data' => $records, 'paginationLinks' => $paginationLinks];
    }

    public function getThreeLastRecords($userId)
    {
        return BMI::where('user_id', $userId)->latest()->take(3)->get();
    }

    public function getLastSevenRecords($userId)
    {
        return BMI::where('user_id', $userId)->orderByDesc('created_at')->take(7)->get();
    }
}
