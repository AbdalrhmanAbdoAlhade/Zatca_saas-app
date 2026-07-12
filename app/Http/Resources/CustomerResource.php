<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'vat_number' => $this->vat_number,
            'commercial_registration_number' => $this->commercial_registration_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => [
                'country' => $this->country,
                'city' => $this->city,
                'district' => $this->district,
                'street' => $this->street,
                'building_number' => $this->building_number,
                'additional_number' => $this->additional_number,
                'postal_code' => $this->postal_code,
            ],
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
