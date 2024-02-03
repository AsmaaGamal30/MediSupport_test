<?php

namespace App\Http\Resources\BloodSugar;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LastBloodSugarResource extends JsonResource
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
        ];
    }
}
