<?php

namespace App\Http\Resources\BloodSugar;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BloodSugarResource extends JsonResource
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
            'level' => $this->level,
            'created_at' => $this->created_at->format('Y-m-d'),
            'day-name' => substr($this->created_at->format('l'), 0, 3),
        ];
    }
}
