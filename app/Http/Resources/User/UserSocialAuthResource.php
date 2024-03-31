<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSocialAuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->provider_name == 'facebook') {
            return [
                'first_name' => $this->name,
                'email' => $this->email,
                'provider_name' => $this->provider_name
            ];
        }

        if ($this->provider_name == 'google') {
            return [
                'first_name' => $this->name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'provider_name' => $this->provider_name
            ];
        }
    }
}
