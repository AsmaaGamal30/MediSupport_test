<?php

namespace App\Http\Resources\BloodSugar;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BloodSugarHistoryResource extends JsonResource
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
            'advice' => $this->bloodSugarAdvice->key,
            'level' => $this->level,
            'created_at' => $this->created_at->format('Y-m-d'),
            'day-name' => substr($this->created_at->format('l'), 0, 3),
        ];
    }
}
