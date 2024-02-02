<?php

namespace App\Http\Resources\BMI;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BMIHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'result' => $this->result,
            'key' => $this->bmiAdvice ? $this->bmiAdvice->key : null,
            'created_at' => $this->created_at,
        ];

    }
}