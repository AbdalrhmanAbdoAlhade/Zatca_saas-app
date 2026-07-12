<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'sku' => $this->sku,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'unit_price' => $this->unit_price,
            'tax_percentage' => $this->tax_percentage,
            'unit' => $this->unit,
            'track_stock' => $this->track_stock,
            'stock_quantity' => $this->when($this->track_stock, $this->stock_quantity),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
