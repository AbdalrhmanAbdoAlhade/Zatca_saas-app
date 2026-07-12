<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'invoice_number' => $this->invoice_number,
            'invoice_type' => $this->invoice_type,
            'invoice_status' => $this->invoice_status,
            'payment_method' => $this->payment_method,

            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),

            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,

            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'notes' => $this->notes,

            'icv' => $this->icv,
            'invoice_hash' => $this->invoice_hash,
            'qr_code' => $this->qr_code,
            'zatca_status' => $this->zatca_status,

            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),

            'created_at' => $this->created_at,
        ];
    }
}
