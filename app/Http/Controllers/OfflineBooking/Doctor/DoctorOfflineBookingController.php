<?php

namespace App\Http\Controllers\OfflineBooking\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfflineBookingRequests\{AddAppointmentRequest, DeleteAppointmentRequest, StoreDateRequest, StoreTimeRequest, UpdateAppointmentRequest};
use App\Services\OfflineBooking\Doctor\DoctorOfflineBookingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DoctorOfflineBookingController extends Controller
{
    protected $offlineBookingService;

    public function __construct(DoctorOfflineBookingService $offlineBookingService)
    {
        $this->offlineBookingService = $offlineBookingService;
        $this->middleware('auth:doctor');
    }

    public function addAppointment(AddAppointmentRequest $request)
    {
        try {
            $response = $this->offlineBookingService->addAppointment($request);
            return response()->json($response['data'], $response['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getALLAppointment()
    {
        try {
            $response = $this->offlineBookingService->getAllAppointments();
            return response()->json($response['data'], $response['status']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteAppointment(DeleteAppointmentRequest $request)
    {
        try {
            $response = $this->offlineBookingService->deleteAppointment($request);
            return response()->json($response['data'], $response['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateAppointment(UpdateAppointmentRequest $request)
    {
        try {
            $response = $this->offlineBookingService->updateAppointment($request);
            return response()->json($response['data'], $response['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getAllOfflineBooking()
    {
        try {
            $response = $this->offlineBookingService->getAllOfflineBookings();
            return response()->json($response['data'], $response['status']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
