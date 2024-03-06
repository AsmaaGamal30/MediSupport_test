<?php

namespace App\Http\Resources\OfflineBooking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id'=> $this->id,
             'first_name' =>  $this->doctor->first_name,
             'last_name' =>  $this->doctor->last_name,
             'clinic_location' => $this->doctor->clinic_location,
             'time' => $this->time->time, 
             'date' => $this->date->date,
        ];
    }
}
