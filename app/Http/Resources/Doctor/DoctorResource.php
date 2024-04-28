<?php

namespace App\Http\Resources\Doctor;

use Illuminate\Http\Request;
use App\Traits\AverageRatingTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    use AverageRatingTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $averageRating = $this->calculateAverageRating($this->rates);

        return [
            'id'=> $this->id,
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
            'working_hours' => $this->working_hours,
        ];
    }

    /**
     * Calculate the average rating of the doctor.
     *
     * @return float|null
     */
    private function calculateAverageRating(): ?float
    {
        $ratings = $this->rates;

        if ($ratings->isEmpty()) {
            return null;
        }

        $totalRating = $ratings->sum('rate');
        $ratingsCount = $ratings->count();

        return round($totalRating / $ratingsCount, 1);
    }
}
