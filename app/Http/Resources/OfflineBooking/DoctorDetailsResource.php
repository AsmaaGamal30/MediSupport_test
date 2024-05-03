<?php

namespace App\Http\Resources\OfflineBooking;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userRating = $request->user()->rates()->where('doctor_id', $this->id)->first();
        
        $currentDate = Carbon::today();

        $newDates = $this->dates()
            ->where('date', '>=', $currentDate)
            ->get(['id', 'date']);

        return [
            'id'=> $this->id,
            'first_name' =>  $this->first_name ,
            'last_name' => $this->last_name,
            'specialization' => $this->specialization,
            'Avatar' => $this->avatar,
            'photo' => $this->photo,
            'price' => $this->price,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'clinic_location' => $this->clinic_location,
            'avg_rating' => number_format($this->rates->avg('rate'),1),
            'user_rating' => $userRating ? $userRating->rate : null,
            'dates' => $newDates,
        ];
    }
}
