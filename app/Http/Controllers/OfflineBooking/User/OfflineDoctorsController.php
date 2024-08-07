<?php

namespace App\Http\Controllers\OfflineBooking\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfflineBookingRequests\PageRequest;
use App\Http\Requests\OfflineBookingRequests\SearchPageRequest;
use App\Http\Resources\Booking\DoctorInfoResource;
use App\Http\Resources\OfflineBooking\OfflineDoctorsResource;
use App\Models\Doctor;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OfflineDoctorsController extends Controller
{
    use ApiResponse;

    public function selectTopDoctorsByRating()
    {
        try {
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

            return $this->apiResponse(
                data: $data,
                message: "Top 10 doctors sorted by rating",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select doctors', 500);
        }
    } //end selectTopDoctorsByRating

    public function selectDoctors()
    {
        try {
            $doctors = Doctor::paginate(10);

            $data = OfflineDoctorsResource::collection($doctors);

            return $this->apiResponse(
                data: [
                    'current_page' => $doctors->currentPage(),
                    'last_page' => $doctors->lastPage(),
                    'data' => $data,
                ],
                message: "Doctors sorted by rating",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to select doctors', 500);
        }
    } //end selectDoctors

    public function searchDoctors(SearchPageRequest $request)
    {
        try {
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

            return $this->apiResponse(
                data: [
                    'current_page' => $request->page,
                    'last_page' => $lastPage,
                    'data' => $data,
                ],
                message: "Doctors filtered and sorted",
                statuscode: 200,
                error: false,
            );
        } catch (\Exception $e) {
            return $this->error('Failed to search doctors', 500);
        }
    } //end searchDoctors
}
