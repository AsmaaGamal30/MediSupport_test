<?php

namespace App\Http\Resources\OnlineBooking;

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
            'id' => $this->id,
            'username' => $this->user->name . ' ' . $this->user->last_name,
            'doctor_name' => $this->doctor->first_name . ' ' . $this->doctor->last_name,
            'status' => $this->status,
            'email' => $this->user->email,
            'created_at' => $this->created_at->format('Y-m-d H:i:s')


        ];
    }
}