<?php

namespace App\Http\Resources\BloodPressure;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BloodPressureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string)$this->id,
            'attributes' => [
                'user_id' => (string)$this->user->id,
                'systolic' => $this->systolic,
                'diastolic' => $this->diastolic,
                'created_at' => $this->created_at->format('Y-m-d'),
                'day-name' => substr($this->created_at->format('l'), 0, 3),
                'pressure_advice_id' => (string)$this->pressureAdvice->id,
                'pressure_advice_key' => $this->pressureAdvice->key,
                'pressure_advice_advice' => $this->pressureAdvice->advice
            ],
        ];
    }
}
