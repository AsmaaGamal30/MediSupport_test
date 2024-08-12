<?php

namespace App\Services\OfflineBooking\User;

use App\Http\Resources\OfflineBooking\OfflineDoctorsResource;
use App\Models\Doctor;

class OfflineDoctorsService
{
    public function selectTopDoctorsByRating()
    {
        $maxDoctors = 10;

        $doctors = Doctor::with('rates')
            ->withCount('rates')
            ->get()
            ->sortByDesc(function ($doctor) {
                $avgRating = $doctor->rates_count > 0 ? $doctor->rates->avg('rate') : 0;
                return [$avgRating, $doctor->rates_count];
            })
            ->take($maxDoctors);

        $data = OfflineDoctorsResource::collection($doctors);

        return [
            'data' => $data,
            'status' => 200
        ];
    }

    public function selectDoctors()
    {
        $doctors = Doctor::paginate(10);

        $data = OfflineDoctorsResource::collection($doctors);

        return [
            'data' => [
                'current_page' => $doctors->currentPage(),
                'last_page' => $doctors->lastPage(),
                'data' => $data,
            ],
            'status' => 200
        ];
    }

    public function searchDoctors($request)
    {
        $query = Doctor::query();

        $searchTerm = $request->input('search');
        $query->where('first_name', 'like', "%$searchTerm%")
            ->orWhere('last_name', 'like', "%$searchTerm%")
            ->orWhere('specialization', 'like', "%$searchTerm%");

        $totalDoctorsCount = $query->count();

        $pageSize = 10;
        $lastPage = ceil($totalDoctorsCount / $pageSize);

        $doctors = $query->with('rates')
            ->withCount('rates')
            ->get()
            ->sortByDesc(function ($doctor) {
                $avgRating = $doctor->rates_count > 0 ? $doctor->rates->avg('rate') : 0;
                return [$avgRating, $doctor->rates_count];
            })
            ->forPage($request->page, $pageSize);

        $data = OfflineDoctorsResource::collection($doctors);

        return [
            'data' => [
                'current_page' => $request->page,
                'last_page' => $lastPage,
                'data' => $data,
            ],
            'status' => 200
        ];
    }
}
