<?php

namespace App\Http\Controllers\OfflineBooking\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfflineBookingRequests\{PageRequest, SearchPageRequest};
use App\Services\OfflineBooking\User\OfflineDoctorsService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OfflineDoctorsController extends Controller
{
    protected $doctorService;

    public function __construct(OfflineDoctorsService $doctorService)
    {
        $this->doctorService = $doctorService;
        $this->middleware('auth:user');
    }

    public function selectTopDoctorsByRating()
    {
        try {
            $response = $this->doctorService->selectTopDoctorsByRating();
            return response()->json($response['data'], $response['status']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function selectDoctors()
    {
        try {
            $response = $this->doctorService->selectDoctors();
            return response()->json($response['data'], $response['status']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function searchDoctors(SearchPageRequest $request)
    {
        try {
            $response = $this->doctorService->searchDoctors($request);
            return response()->json($response['data'], $response['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
