<?php

namespace App\Http\Resources\BMI;

use Illuminate\Http\Resources\Json\JsonResource;

class BMIResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Ensure the 'advice' property is not null
        $advice = $this->bmiAdvice ? $this->bmiAdvice->advice : 'No advice available';

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'result' => $this->result,
            'advice' => $advice,
            'key' => $this->bmiAdvice ? $this->bmiAdvice->key : null, 
            'created_at' => $this->created_at,

        ];
    }
}