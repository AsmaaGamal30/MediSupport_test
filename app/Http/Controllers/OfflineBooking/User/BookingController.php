<?php

namespace App\Http\Controllers\OfflineBooking\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfflineBookingRequests\{BookingIdRequest, BookingRequest, DateIdRequest, DoctorIdRequest};
use App\Services\OfflineBooking\User\BookingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
        $this->middleware('auth:user');
    }

    public function getDoctorDetails(DoctorIdRequest $request)
    {
        try {
            $response = $this->bookingService->getDoctorDetails($request);
            return response()->json($response['data'], $response['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getDoctorDateTimes(DateIdRequest $request)
    {
        try {
            $response = $this->bookingService->getDoctorDateTimes($request);
            return response()->json($response['data'], $response['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function BookAppointment(BookingRequest $request)
    {
        try {
            $response = $this->bookingService->bookAppointment($request);
            return response()->json($response['data'], $response['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function selectUserBooking()
    {
        try {
            $response = $this->bookingService->selectUserBooking();
            return response()->json($response['data'], $response['status']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteBooking(BookingIdRequest $request)
    {
        try {
            $response = $this->bookingService->deleteBooking($request);
            return response()->json($response['data'], $response['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
