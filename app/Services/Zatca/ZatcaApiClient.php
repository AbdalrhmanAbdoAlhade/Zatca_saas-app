<?php

namespace App\Services\Zatca;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZatcaApiClient
{
    public function __construct(protected string $baseUrl, protected string $apiVersion = 'V2')
    {
    }

    /**
     * المرحلة 1: تبادل الـ CSR بـ Compliance CSID عن طريق الـ OTP.
     */
    public function requestComplianceCsid(string $csrBase64, string $otp): array
    {
        $response = Http::withHeaders([
            'Accept-Version' => $this->apiVersion,
            'OTP' => $otp,
        ])->post("{$this->baseUrl}/compliance", [
            'csr' => $csrBase64,
        ]);

        return $this->handle($response);
    }

    /**
     * المرحلة 2: فحص الامتثال - إرسال فاتورة تجريبية موقّعة للتحقق منها.
     */
    public function complianceCheckInvoice(
        string $binarySecurityToken,
        string $secret,
        string $invoiceBase64,
        string $invoiceHash,
        string $uuid,
    ): array {
        $response = Http::withBasicAuth($binarySecurityToken, $secret)
            ->withHeaders(['Accept-Version' => $this->apiVersion])
            ->post("{$this->baseUrl}/compliance/invoices", [
                'invoiceHash' => $invoiceHash,
                'uuid' => $uuid,
                'invoice' => $invoiceBase64,
            ]);

        return $this->handle($response);
    }

    /**
     * المرحلة 3: طلب الـ Production CSID باستخدام الـ Compliance Request ID.
     */
    public function requestProductionCsid(
        string $complianceBinarySecurityToken,
        string $complianceSecret,
        string $complianceRequestId,
    ): array {
        $response = Http::withBasicAuth($complianceBinarySecurityToken, $complianceSecret)
            ->withHeaders(['Accept-Version' => $this->apiVersion])
            ->post("{$this->baseUrl}/production/csids", [
                'compliance_request_id' => $complianceRequestId,
            ]);

        return $this->handle($response);
    }

    protected function handle(Response $response): array
    {
        if ($response->failed()) {
            throw new RuntimeException(
                'zatca_api_error: '.$response->status().' - '.$response->body()
            );
        }

        return $response->json() ?? [];
    }
}
