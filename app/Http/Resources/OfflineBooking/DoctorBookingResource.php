<?php

namespace App\Http\Resources\OfflineBooking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'booking_id' => $this->id,
            'user_id' => $this->user->id,
            'first_name' =>  $this->user->name,
            'last_name' =>  $this->user->last_name,
            'time' => $this->time->time,
            'date' => $this->date->date,
            'created_at' => $this->created_at
        ];
    }
}
