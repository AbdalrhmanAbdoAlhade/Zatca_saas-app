<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminStaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'role' => [
                'id' => $this->role?->id,
                'name' => $this->role?->name,
                'name_ar' => $this->role?->name_ar,
                'slug' => $this->role?->slug,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
