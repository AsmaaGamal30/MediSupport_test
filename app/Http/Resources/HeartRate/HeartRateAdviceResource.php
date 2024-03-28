<?php

namespace App\Http\Resources\HeartRate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeartRateAdviceResource extends JsonResource
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
            'key' => $this->key,
            'advice' => $this->advice,
        ];
    }
}
