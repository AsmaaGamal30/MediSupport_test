<?php

namespace App\Http\Controllers\VideoCall;

use App\Http\Controllers\Controller;
use App\Services\VideoCall\UserVideoCallService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserVideoCallController extends Controller
{
    protected $videoCallService;

    public function __construct(UserVideoCallService $videoCallService)
    {
        $this->videoCallService = $videoCallService;
        $this->middleware('auth:user');
    }

    public function generateToken(Request $request)
    {
        try {
            $response = $this->videoCallService->generateUserToken($request);

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
            $response = $this->videoCallService->startUserCall($request);

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
            $response = $this->videoCallService->endUserCall($request);

            return response()->json($response['data'], $response['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
