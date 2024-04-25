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
        $advice = $this->bmiAdvice ? $this->bmiAdvice->advice : 'No advice available';

        return [
            'user_id' => $this->user_id,
            'result' => $this->result,
            'key' => $this->bmiAdvice ? $this->bmiAdvice->key : null,
            'advice' => $advice,
            'created_at' => $this->created_at,
            'day-name' => substr($this->created_at->format('l'), 0, 3),

        ];

    }
}