<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trade_name_ar' => $this->trade_name_ar,
            'trade_name_en' => $this->trade_name_en,
            'vat_number' => $this->vat_number,
            'commercial_registration_number' => $this->commercial_registration_number,
            'status' => $this->status,
            'default_locale' => $this->default_locale,
            'logo_path' => $this->logo_path,
        ];
    }
}
