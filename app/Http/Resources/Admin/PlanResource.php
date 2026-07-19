<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'slug' => $this->slug,
            'price_monthly' => $this->price_monthly,
            'price_yearly' => $this->price_yearly,
            'max_users' => $this->max_users,
            'max_invoices' => $this->max_invoices,
            'zatca_integration' => $this->zatca_integration,
            'reports_access' => $this->reports_access,
            'is_active' => $this->is_active,
            'subscriptions_count' => $this->subscriptions_count,
        ];
    }
}
