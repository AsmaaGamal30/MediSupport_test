<?php

namespace App\Http\Controllers\Rating;

use App\Models\Doctor;
use App\Models\Rating;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\RatingRequests\RatingRequest;

class RatingController extends Controller
{
    use ApiResponse;


    public function store(RatingRequest  $request)
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




}