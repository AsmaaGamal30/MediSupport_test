<?php

namespace App\Http\Controllers\VideoCall;

use App\Http\Controllers\Controller;
use App\Services\VideoCall\DoctorVideoCallService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DoctorVideoCallController extends Controller
{
    protected $videoCallService;

    public function __construct(DoctorVideoCallService $videoCallService)
    {
        $this->videoCallService = $videoCallService;
        $this->middleware('auth:doctor');
    }

    public function generateToken(Request $request)
    {
        try {
            $response = $this->videoCallService->generateToken($request);

            return response()->json($response['data'], $response['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function startCall(Request $request)
    {
        try {
            $response = $this->videoCallService->startCall($request);

            return response()->json($response['data'], $response['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function endCall(Request $request)
    {
        try {
            $response = $this->videoCallService->endCall($request);

            return response()->json($response['data'], $response['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
