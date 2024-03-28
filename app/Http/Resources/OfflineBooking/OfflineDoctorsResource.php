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
            'Avatar' => $this->avatar,
            'clinic_location' => $this->clinic_location,
            'working_hours' => $this->working_hours,
            'rate' =>  number_format($this->rates->avg('rate'),1),

        ];
    }
}
