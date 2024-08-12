<?php

namespace App\Services\HealthMatrix;

use App\Models\HeartRate;
use App\Models\HeartRateAdvice;

class HeartRateService
{
    public function determineHeartRateAdvice($heartRate)
    {
        if ($heartRate < 60) {
            return HeartRateAdvice::where('key', 'low')->first();
        } elseif ($heartRate >= 60 && $heartRate <= 100) {
            return HeartRateAdvice::where('key', 'normal')->first();
        } else {
            return HeartRateAdvice::where('key', 'high')->first();
        }
    }

    public function storeHeartRate($userId, $heartRate, $advice)
    {
        HeartRate::create([
            'heart_rate' => $heartRate,
            'user_id' => $userId,
            'heart_rate_advice_id' => $advice->id
        ]);
    }

    public function getAllHeartRateRecords($userId)
    {
        $heartRateRecords = HeartRate::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(10);

        return [
            'data' => $heartRateRecords,
            'pagination' => [
                'current_page' => $heartRateRecords->currentPage(),
                'last_page' => $heartRateRecords->lastPage(),
            ],
        ];
    }

    public function getLastSevenHeartRateRecords($userId)
    {
        return HeartRate::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(7)
            ->get();
    }

    public function getLastThreeHeartRateRecords($userId)
    {
        return HeartRate::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();
    }

    public function getLastHeartRateRecord($userId)
    {
        return HeartRate::where('user_id', $userId)
            ->latest()
            ->first();
    }

    public function getUserRecommendedAdvice($userId)
    {
        $latestHeartRate = $this->getLastHeartRateRecord($userId);

        return $latestHeartRate ? $latestHeartRate->heartRateAdvice : null;
    }
}
