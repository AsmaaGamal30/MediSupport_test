<?php

namespace App\Services\HealthMatrix;

use App\Models\BloodSugar;
use App\Models\BloodSugarAdvice;
use Illuminate\Support\Facades\Auth;

class BloodSugarService
{
    public function storeBloodSugar($validatedData)
    {
        $userAuthId = Auth::guard('user')->user()->id;
        $bloodSugarLevel = $validatedData['level'];
        $statusId = $validatedData['blood_sugar_statuses_id'];
        $advice = $this->determineSugarAdvice($bloodSugarLevel, $statusId);

        BloodSugar::create([
            'level' => $bloodSugarLevel,
            'user_id' => $userAuthId,
            'blood_sugar_statuses_id' => $statusId,
            'blood_sugar_advice_id' => $advice->id,
        ]);
    }

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

        return null;
    }

    public function getAllBloodSugarRecords()
    {
        $userAuthId = Auth::guard('user')->user()->id;
        return BloodSugar::where('user_id', $userAuthId)
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function getLastThreeBloodSugarRecords()
    {
        $userAuthId = Auth::guard('user')->user()->id;
        return BloodSugar::where('user_id', $userAuthId)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();
    }

    public function getLastSevenBloodSugarRecords()
    {
        $userAuthId = Auth::guard('user')->user()->id;
        return BloodSugar::where('user_id', $userAuthId)
            ->orderByDesc('created_at')
            ->take(7)
            ->get();
    }

    public function getLastBloodSugarRecord()
    {
        $userAuthId = Auth::guard('user')->user()->id;
        return BloodSugar::where('user_id', $userAuthId)
            ->latest()
            ->first();
    }

    public function getUserRecommendedAdvice()
    {
        $userAuthId = Auth::guard('user')->user()->id;
        $latestBloodSugar = BloodSugar::where('user_id', $userAuthId)
            ->latest()
            ->first();

        if ($latestBloodSugar) {
            return $latestBloodSugar->bloodSugarAdvice;
        }

        return null;
    }
}
