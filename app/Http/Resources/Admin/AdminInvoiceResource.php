<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminInvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'invoice_number' => $this->invoice_number,
            'invoice_type' => $this->invoice_type,
            'invoice_status' => $this->invoice_status,
            'zatca_status' => $this->zatca_status,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'issue_date' => $this->issue_date,
            'icv' => $this->icv,
            'company' => [
                'id' => $this->company?->id,
                'trade_name_ar' => $this->company?->trade_name_ar,
                'trade_name_en' => $this->company?->trade_name_en,
            ],
            'customer' => $this->customer?->name_ar,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'name_ar' => $item->name_ar,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_amount' => $item->total_amount,
            ])),
            'created_at' => $this->created_at,
        ];
    }
}
