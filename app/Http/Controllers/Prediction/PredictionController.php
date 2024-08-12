<?php

namespace App\Http\Controllers\Prediction;

use App\Http\Controllers\Controller;
use App\Services\Prediction\PredictionService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;

class PredictionController extends Controller
{
    protected $predictionService;

    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    public function predict(Request $request)
    {
        try {
            $user = Auth::user(); // Get the currently authenticated user
            $result = $this->predictionService->predict($request, $user);

            return response()->json($result['data'], $result['status']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
