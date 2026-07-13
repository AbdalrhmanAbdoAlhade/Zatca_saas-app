<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Zatca\ComplianceCheckRequest;
use App\Http\Requests\Zatca\GenerateComplianceCsidRequest;
use App\Http\Resources\ZatcaSettingResource;
use App\Services\Zatca\ZatcaOnboardingService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ZatcaController extends Controller
{
    use ApiResponse;

    public function __construct(protected ZatcaOnboardingService $onboardingService)
    {
    }

    public function show(Request $request)
    {
        return $this->success(new ZatcaSettingResource($request->user()->company->zatcaSettings));
    }

    public function generateComplianceCsid(GenerateComplianceCsidRequest $request)
    {
        try {
            $settings = $this->onboardingService->generateComplianceCsid(
                $request->user()->company,
                $request->validated('otp'),
            );
        } catch (RuntimeException $e) {
            return $this->error(__('zatca.'.$e->getMessage()), 422);
        }

        return $this->success(new ZatcaSettingResource($settings), __('zatca.compliance_csid_generated'));
    }

    public function complianceCheck(ComplianceCheckRequest $request)
    {
        try {
            $settings = $this->onboardingService->checkCompliance(
                $request->user()->company,
                $request->validated('invoice_base64'),
                $request->validated('invoice_hash'),
                $request->validated('uuid'),
            );
        } catch (RuntimeException $e) {
            return $this->error(__('zatca.'.$e->getMessage()), 422);
        }

        return $this->success(new ZatcaSettingResource($settings), __('zatca.compliance_check_done'));
    }

    public function requestProductionCsid(Request $request)
    {
        try {
            $settings = $this->onboardingService->requestProductionCsid($request->user()->company);
        } catch (RuntimeException $e) {
            return $this->error(__('zatca.'.$e->getMessage()), 422);
        }

        return $this->success(new ZatcaSettingResource($settings), __('zatca.production_csid_generated'));
    }

    public function activateProduction(Request $request)
    {
        try {
            $settings = $this->onboardingService->activateProduction($request->user()->company);
        } catch (RuntimeException $e) {
            return $this->error(__('zatca.'.$e->getMessage()), 422);
        }

        return $this->success(new ZatcaSettingResource($settings), __('zatca.production_activated'));
    }
}
