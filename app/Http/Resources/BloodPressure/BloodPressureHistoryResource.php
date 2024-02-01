<?php

namespace App\Http\Resources\BloodPressure;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BloodPressureHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pagination = $this->resource->toArray();

        return [
            'id' => (string)$this->id,
            'attributes' => [
                'user_id' => (string)$this->user->id,
                'systolic' => $this->systolic,
                'diastolic' => $this->diastolic,
                'created_at' => $this->created_at,
                'pressure_advice_key' => $this->pressureAdvice->key,
            ],
        ];
    }
}
