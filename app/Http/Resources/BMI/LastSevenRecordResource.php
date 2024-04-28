<?php

namespace App\Http\Resources\BMI;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LastSevenRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'result' => $this->result,
            'created_at' => $this->created_at,
            'day-name' => substr($this->created_at->format('l'), 0, 3),

        ];
    }
}