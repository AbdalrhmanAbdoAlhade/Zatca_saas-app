<?php

namespace App\Services\Zatca;

use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Saleh7\Zatca\Helpers\Certificate;
use Saleh7\Zatca\ZatcaAPI;

/**
 * إرسال الفاتورة الموقّعة فعلياً لـ ZATCA بعد ما ZatcaInvoiceSigningService توقّعها.
 *
 * القاعدة:
 *   - standard/tax invoice, credit/debit note على فاتورة standard  → Clearance (real-time، ZATCA بترجع نسخة مختومة)
 *   - simplified_tax_invoice (ومشتقاتها)                            → Reporting (async، مجرد إقرار استلام)
 *
 * ⚠️ الـ signature بتاعة submitReportingInvoice/submitClearanceInvoice في المكتبة
 * ممكن تختلف شوية حسب النسخة المثبتة (زي ما حصل مع InvoiceSigner قبل كده) -
 * راجعها في الـ README بتاع النسخة عندك وعدّل الاستدعاء هنا لو لزم الأمر.
 */
class ZatcaSubmissionService
{
    public function submit(Invoice $invoice): Invoice
    {
        if (! class_exists(ZatcaAPI::class)) {
            throw new RuntimeException('saleh7_zatca_package_not_installed');
        }

        if (! $invoice->xml_path || ! $invoice->invoice_hash) {
            throw new RuntimeException('invoice_not_signed_yet');
        }

        $zatcaSettings = $invoice->company->zatcaSettings;

        if (! $zatcaSettings || ! $zatcaSettings->production_certificate) {
            throw new RuntimeException('company_certificate_not_ready');
        }

        $signedXml = Storage::disk('local')->get($invoice->xml_path);
        $signedXmlBase64 = base64_encode($signedXml);

        $certificate = new Certificate(
            $zatcaSettings->certificate,
            $zatcaSettings->private_key,
            $zatcaSettings->secret_key ?? '',
        );

        $api = new ZatcaAPI($zatcaSettings->environment === 'production' ? 'production' : 'sandbox');

        $isSimplified = in_array($invoice->invoice_type, ['simplified_tax_invoice'], true);

        try {
            $response = $isSimplified
                ? $api->submitReportingInvoice($signedXmlBase64, $invoice->invoice_hash, $invoice->uuid, $certificate)
                : $api->submitClearanceInvoice($signedXmlBase64, $invoice->invoice_hash, $invoice->uuid, $certificate);
        } catch (\Throwable $e) {
            $invoice->update([
                'zatca_status' => 'rejected',
                'zatca_response' => ['error' => $e->getMessage()],
                'zatca_submitted_at' => now(),
            ]);

            throw new RuntimeException('zatca_submission_failed: '.$e->getMessage());
        }

        $status = $this->resolveStatus($response, $isSimplified);

        // في حالة الـ Clearance، ZATCA بترجع نسخة مختومة من الـ XML - نستبدلها بالمخزنة عندنا
        if (! $isSimplified && method_exists($response, 'getClearedInvoice') && $response->getClearedInvoice()) {
            Storage::disk('local')->put($invoice->xml_path, $response->getClearedInvoice());
        }

        $invoice->update([
            'zatca_uuid' => $invoice->uuid,
            'zatca_status' => $status,
            'zatca_response' => method_exists($response, 'toArray') ? $response->toArray() : (array) $response,
            'zatca_submitted_at' => now(),
            'invoice_status' => in_array($status, ['cleared', 'reported'], true) ? 'submitted' : $invoice->invoice_status,
        ]);

        return $invoice->fresh();
    }

    protected function resolveStatus($response, bool $isSimplified): string
    {
        if (method_exists($response, 'hasErrors') && $response->hasErrors()) {
            return 'rejected';
        }

        if (method_exists($response, 'hasWarnings') && $response->hasWarnings()) {
            return 'warning';
        }

        if (! method_exists($response, 'isSuccess') || $response->isSuccess()) {
            return $isSimplified ? 'reported' : 'cleared';
        }

        return 'rejected';
    }
}
