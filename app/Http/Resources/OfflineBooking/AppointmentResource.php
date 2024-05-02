<?php

namespace App\Http\Resources\OfflineBooking;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $date = Carbon::parse($this->date);
        return [
            'id' => $this->id,
            'date' => $this->date,
            'day_name' => $date->format('l'), 
            'times' => TimeResource::collection($this->times),
        ];
    }
}
