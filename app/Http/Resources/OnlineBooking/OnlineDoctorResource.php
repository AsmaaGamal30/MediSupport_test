<?php

namespace App\Http\Resources\OnlineBooking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class OnlineDoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Calculate average rating
        $averageRating = $this->rates->isEmpty() ? null : round($this->rates->avg('rate'), 1);
        
        // Get authenticated user
        $user = Auth::user();
        
        // Get user's rating for this doctor if available
        $userRating = $user ? $this->rates->where('user_id', $user->id)->first() : null;

        return [
            'id' => $this->id,
            'admin_id' => $this->admin_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'phone' => $this->phone,
            'specialization' => $this->specialization,
            'bio' => $this->bio,
            'price' => $this->price,
            'clinic_location' => $this->clinic_location,
            'active_status' => $this->active_status,
            'average_rating' => $averageRating,
            'user_rating' => $userRating ? $userRating->rate : null,
            'working_hours' => $this->working_hours,
        ];
    }
}