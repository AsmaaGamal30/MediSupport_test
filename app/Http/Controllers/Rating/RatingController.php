<?php

namespace App\Http\Controllers\Rating;

use App\Http\Controllers\Controller;
use App\Services\Rating\RatingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RatingController extends Controller
{
    protected $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    public function store(Request $request)
    {
        try {
            $result = $this->ratingService->storeRating($request);

            return response()->json($result['data'], $result['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
