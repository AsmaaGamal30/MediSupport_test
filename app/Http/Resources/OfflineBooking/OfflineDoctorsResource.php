<?php

namespace App\Http\Resources\OfflineBooking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfflineDoctorsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,
            'first_name' =>  $this->first_name,
            'last_name' => $this->last_name,
            'photo' => $this->photo,
            'clinic_location' => $this->clinic_location,
            'working_hours' => $this->working_hours,
            'rate' =>  $this->rates->avg('rate'),

        ];
    }
}