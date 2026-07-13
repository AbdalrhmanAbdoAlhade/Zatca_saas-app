<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZatcaSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'environment' => $this->environment,
            'compliance_status' => $this->compliance_status,
            'onboarding_stage' => $this->onboarding_stage,
            'onboarding_stage_label' => match ($this->onboarding_stage) {
                0 => 'not_started',
                1 => 'compliance_csid_generated',
                2 => 'compliance_check_passed',
                3 => 'production_csid_generated',
                4 => 'production_active',
                default => 'unknown',
            },
            'certificate_expiry_date' => $this->certificate_expiry_date,
            'last_synced_at' => $this->last_synced_at,
        ];
    }
}
