<?php

namespace App\Services\Rating;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Rating;
use App\Models\Doctor;

class RatingService
{
    public function storeRating(Request $request)
    {
        // Validate request data
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'rate' => 'required|integer|min:1|max:5'
        ]);

        $user = Auth::guard('user')->user();

        // Check if the user has already rated the doctor
        $existingRating = Rating::where('user_id', $user->id)
            ->where('doctor_id', $request->doctor_id)
            ->exists();

        if ($existingRating) {
            throw ValidationException::withMessages(['You have already rated this doctor']);
        }

        // Create a new Rating instance
        Rating::create([
            'user_id' => $user->id,
            'doctor_id' => $request->doctor_id,
            'rate' => $request->rate
        ]);

        return [
            'data' => ['message' => 'Rating stored successfully'],
            'status' => 200
        ];
    }
}
