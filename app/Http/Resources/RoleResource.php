<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_system' => $this->is_system,
            'users_count' => $this->users_count,
            'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions->map(fn ($p) => [
                'id' => $p->id,
                'slug' => $p->slug,
                'name_ar' => $p->name_ar,
                'group' => $p->group,
            ])),
        ];
    }
}
