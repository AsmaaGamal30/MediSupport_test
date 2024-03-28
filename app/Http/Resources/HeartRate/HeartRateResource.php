<?php

namespace App\Http\Resources\HeartRate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeartRateResource extends JsonResource
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
            'user_id'=>$this->user->id,
            'advice' => new HeartRateAdviceResource($this->heartRateAdvice),
            'heart_rate' => $this->heart_rate,
            'created_at' => $this->created_at->format('Y-m-d'),
            'day-name' => substr($this->created_at->format('l'), 0, 3),
        ];
    }
}
