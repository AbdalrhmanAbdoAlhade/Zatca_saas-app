<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'preferred_locale' => $this->preferred_locale,
            'role' => [
                'id' => $this->role?->id,
                'name' => $this->role?->name,
                'name_ar' => $this->role?->name_ar,
                'slug' => $this->role?->slug,
            ],
            'company' => new CompanyResource($this->whenLoaded('company')),
        ];
    }
}
