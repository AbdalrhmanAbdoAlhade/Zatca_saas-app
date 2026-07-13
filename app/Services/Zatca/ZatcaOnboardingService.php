<?php

namespace App\Services\Zatca;

use App\Models\Company;
use App\Models\CompanyZatcaSetting;
use RuntimeException;

class ZatcaOnboardingService
{
    public function __construct(protected ZatcaCsrGenerator $csrGenerator)
    {
    }

    /**
     * المرحلة 1: توليد CSR + طلب Compliance CSID من ZATCA.
     */
    public function generateComplianceCsid(Company $company, string $otp): CompanyZatcaSetting
    {
        $settings = $this->settingsOrFail($company);

        $csrData = [
            'environment' => $settings->environment,
            'organization_identifier' => $company->vat_number,
            'organization_unit_name' => $company->city ?? 'HQ',
            'organization_name' => $company->trade_name_en ?? $company->trade_name_ar,
            'common_name' => $company->trade_name_en ?? $company->trade_name_ar,
            'egs_serial_number' => sprintf('1-%s|2-%s|3-%s', config('zatca.egs.solution_name'), config('zatca.egs.model'), $company->id),
            'invoice_type' => '1100', // Standard + Simplified مفعّلين، Tax/Zakat مش مفعّلين - عدّلها حسب نوع نشاط كل شركة
            'location' => $company->city ?? $company->country ?? 'SA',
            'business_category' => 'Other',
        ];

        $generated = $this->csrGenerator->generate($csrData);

        $apiClient = $this->apiClientFor($settings->environment);
        $response = $apiClient->requestComplianceCsid(base64_encode($generated['csr']), $otp);

        $settings->update([
            'otp' => $otp,
            'csr' => $generated['csr'],
            'private_key' => $generated['private_key'],
            'request_id' => $response['requestID'] ?? null,
            'compliance_request_id' => $response['requestID'] ?? null,
            'binary_security_token' => $response['binarySecurityToken'] ?? null,
            'secret_key' => $response['secret'] ?? null,
            'compliance_status' => 'pending',
            'onboarding_stage' => 1,
            'last_synced_at' => now(),
        ]);

        return $settings->fresh();
    }

    /**
     * المرحلة 2: فحص الامتثال - بتتنادى مرة لكل نوع فاتورة تجريبية مطلوب من ZATCA
     * (Standard, Simplified, Credit Note, Debit Note ...) لحد ما كلهم ينجحوا.
     */
    public function checkCompliance(Company $company, string $invoiceBase64, string $invoiceHash, string $uuid): CompanyZatcaSetting
    {
        $settings = $this->settingsOrFail($company);

        if (! $settings->binary_security_token || ! $settings->secret_key) {
            throw new RuntimeException('compliance_csid_not_generated_yet');
        }

        $apiClient = $this->apiClientFor($settings->environment);

        $response = $apiClient->complianceCheckInvoice(
            $settings->binary_security_token,
            $settings->secret_key,
            $invoiceBase64,
            $invoiceHash,
            $uuid,
        );

        // ZATCA بترجع validationResults فيها reportingStatus/clearanceStatus - لو PASS نعتبرها ناجحة.
        $passed = collect($response['validationResults']['validationSteps'] ?? [])
            ->every(fn ($step) => ($step['status'] ?? null) === 'PASS');

        $settings->update([
            'compliance_status' => $passed ? 'passed' : 'failed',
            'last_synced_at' => now(),
        ]);

        return $settings->fresh();
    }

    /**
     * المرحلة 3: طلب Production CSID (لازم فحص الامتثال يكون عدى بنجاح الأول).
     */
    public function requestProductionCsid(Company $company): CompanyZatcaSetting
    {
        $settings = $this->settingsOrFail($company);

        if ($settings->compliance_status !== 'passed') {
            throw new RuntimeException('compliance_check_not_passed');
        }

        $apiClient = $this->apiClientFor($settings->environment);

        $response = $apiClient->requestProductionCsid(
            $settings->binary_security_token,
            $settings->secret_key,
            $settings->compliance_request_id,
        );

        $settings->update([
            'production_certificate' => $response['binarySecurityToken'] ?? null,
            'binary_security_token' => $response['binarySecurityToken'] ?? $settings->binary_security_token,
            'secret_key' => $response['secret'] ?? $settings->secret_key,
            'request_id' => $response['requestID'] ?? $settings->request_id,
            'onboarding_stage' => 3,
            'last_synced_at' => now(),
        ]);

        return $settings->fresh();
    }

    /**
     * المرحلة 4: تفعيل بيئة الإنتاج فعلياً بعد التأكد من كل الخطوات السابقة.
     */
    public function activateProduction(Company $company): CompanyZatcaSetting
    {
        $settings = $this->settingsOrFail($company);

        if ($settings->onboarding_stage < 3 || ! $settings->production_certificate) {
            throw new RuntimeException('production_csid_not_ready');
        }

        $settings->update([
            'environment' => 'production',
            'onboarding_stage' => 4,
            'last_synced_at' => now(),
        ]);

        return $settings->fresh();
    }

    protected function settingsOrFail(Company $company): CompanyZatcaSetting
    {
        $settings = $company->zatcaSettings;

        if (! $settings) {
            throw new RuntimeException('zatca_settings_not_found');
        }

        return $settings;
    }

    protected function apiClientFor(string $environment): ZatcaApiClient
    {
        $baseUrl = config("zatca.base_urls.{$environment}", config('zatca.base_urls.sandbox'));

        return new ZatcaApiClient($baseUrl, config('zatca.api_version', 'V2'));
    }
}
