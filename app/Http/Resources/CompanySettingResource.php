<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanySettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'invoice_logo_path' => $this->invoice_logo_path,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'show_qr' => $this->show_qr,
            'show_vat' => $this->show_vat,
            'show_cr' => $this->show_cr,
            'default_tax_percentage' => $this->default_tax_percentage,
            'invoice_pdf_language' => $this->invoice_pdf_language,
        ];
    }
}
