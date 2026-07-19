<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trade_name_ar' => $this->trade_name_ar,
            'trade_name_en' => $this->trade_name_en,
            'vat_number' => $this->vat_number,
            'status' => $this->status,
            'users_count' => $this->users_count,
            'created_at' => $this->created_at,
            'zatca_environment' => $this->whenLoaded('zatcaSettings', fn () => $this->zatcaSettings?->environment),
            'zatca_onboarding_stage' => $this->whenLoaded('zatcaSettings', fn () => $this->zatcaSettings?->onboarding_stage),
            'subscription' => $this->whenLoaded('activeSubscription', fn () => $this->activeSubscription ? [
                'plan' => $this->activeSubscription->plan?->name,
                'status' => $this->activeSubscription->status,
                'end_date' => $this->activeSubscription->end_date,
                'invoices_used' => $this->activeSubscription->invoices_used,
                'invoices_limit' => $this->activeSubscription->invoices_limit,
            ] : null),
        ];
    }
}
