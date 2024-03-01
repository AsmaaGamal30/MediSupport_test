<?php

namespace App\Http\Resources\Doctor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Controllers\Rating\RatingController;

class DoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Calculate average rating for the doctor
        $ratingController = new RatingController();
        $averageRating = $ratingController->getDoctorAverageRating($request, $this->id);

        return [
            'admin_id' => $this->admin_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'photo' => $this->photo,
            'phone' => $this->phone,
            'specialization' => $this->specialization,
            'bio' => $this->bio,
            'price' => $this->price,
            'clinic_location' => $this->clinic_location,
            'average_rating' => $averageRating,
        ];
    }
}