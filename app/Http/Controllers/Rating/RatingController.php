<?php

namespace App\Http\Controllers\Rating;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Rating;
use App\Models\Doctor;
use App\Traits\ApiResponse; 

class RatingController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        try {
            // Check if the user is authenticated as a user
            if (!Auth::guard('user')->check()) {
                throw ValidationException::withMessages(['Unauthenticated']);
            }

            $request->validate([
                'doctor_id' => 'required|exists:doctors,id',
                'rate' => 'required|integer|min:1|max:5'
            ]);

            // Get the authenticated user
            $user = Auth::guard('user')->user();

            // Check if the user has already rated the doctor
            $existingRating = Rating::where('user_id', $user->id)
                                    ->where('doctor_id', $request->doctor_id)
                                    ->exists();

            if ($existingRating) {
                throw ValidationException::withMessages(['You have already rated this doctor']);
            }

            // Create a new Rating instance
            $rating = new Rating();
            $rating->user_id = $user->id;
            $rating->doctor_id = $request->doctor_id;
            $rating->rate = $request->rate;
            $rating->save();

            // Return a success response using the ApiResponse trait method
            return $this->success('Rating stored successfully', 200);
        } catch (ValidationException $e) {
            // Return an error response using the ApiResponse trait method
            return $this->error($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            // Return an error response using the ApiResponse trait method
            return $this->error($e->getMessage(), 500);
        }
    }

    public function getDoctorAverageRating(Request $request)
    {
        try {
            $request->validate([
                'doctor_id' => 'required|exists:doctors,id'
            ]);

            // Get the doctor ID from the request body
            $doctorId = $request->input('doctor_id');

            // Check if the user is authenticated as a user
            if (!Auth::guard('user')->check()) {
                throw ValidationException::withMessages(['Unauthenticated']);
            }

            // Get the authenticated user
            $user = Auth::guard('user')->user();

            // Check if the doctor exists
            $doctor = Doctor::find($doctorId);

            if (!$doctor) {
                throw ValidationException::withMessages(['Doctor not found']);
            }

            // Calculate average rating for the specified doctor
            $averageRating = Rating::where('doctor_id', $doctorId)->avg('rate');

            // Convert numeric average rating to star rating
            $starRating = $this->convertToStarRating($averageRating);

            // Return the doctor average rating in JSON format using the ApiResponse trait method
            return $this->successData('Doctor average rating', 200, ['average_rating' => $starRating]);
        } catch (ValidationException $e) {
            // Return an error response using the ApiResponse trait method
            return $this->error($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            // Return an error response using the ApiResponse trait method
            return $this->error($e->getMessage(), 500);
        }
    }

    // Helper function to convert numeric rating to star rating
    private static function convertToStarRating($numericRating)
    {
        if ($numericRating === null) {
            return '☆☆☆☆☆'; // If no ratings found, return 0 stars
        }

        $fullStars = intval($numericRating);
        $halfStar = ($numericRating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;

        return str_repeat('★', $fullStars) . str_repeat('½', $halfStar) . str_repeat('☆', $emptyStars);
    }
}