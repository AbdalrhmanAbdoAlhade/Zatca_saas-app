<?php

namespace App\Services\Zatca;

use App\Models\Invoice;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ZatcaInvoiceProcessingService
{
    public function __construct(
        protected ZatcaInvoiceXmlBuilder $xmlBuilder,
        protected ZatcaQrEncoder $qrEncoder,
        protected ZatcaInvoiceSigningService $signingService,
    ) {
    }

    /**
     * بيولّد XML (غير موقّع) + QR أساسي (Tags 1-5) ويحفظهم على الفاتورة.
     *
     * ملحوظة: ده مش الشكل النهائي المعتمد من ZATCA - محتاج يمر على مرحلة
     * التوقيع الرقمي (XAdES) الأول قبل الإرسال الفعلي للـ Reporting/Clearance API.
     * راجع تحذيرات ZatcaInvoiceXmlBuilder و ZatcaQrEncoder.
     */
    public function generateXmlAndQr(Invoice $invoice): Invoice
    {
        $company = $invoice->company;

        $xml = $this->xmlBuilder->build($invoice, Arr::only($company->toArray(), [
            'trade_name_ar', 'vat_number', 'street', 'city', 'building_number', 'postal_code', 'country',
        ]));

        $xmlPath = "zatca-invoices/{$company->id}/{$invoice->uuid}.xml";
        Storage::disk('local')->put($xmlPath, $xml);

        // هاش مبدئي على الـ XML الفعلي (مش نص placeholder زي قبل كده) - لسه محتاج
        // canonicalization (C14N) صحيح قبل ما يبقى معتمد رسمياً من ZATCA.
        $invoiceHash = base64_encode(hash('sha256', $xml, true));

        $qrCode = $this->qrEncoder->encodeBasic(
            sellerName: $company->trade_name_ar,
            vatNumber: $company->vat_number,
            timestamp: $invoice->created_at?->toIso8601String() ?? now()->toIso8601String(),
            totalWithVat: number_format((float) $invoice->total_amount, 2, '.', ''),
            vatTotal: number_format((float) $invoice->tax_amount, 2, '.', ''),
        );

        $invoice->update([
            'xml_path' => $xmlPath,
            'invoice_hash' => $invoiceHash,
            'qr_code' => $qrCode,
        ]);

        return $invoice->fresh();
    }

    /**
     * التوقيع الرقمي الفعلي (XAdES) - لازم generateXmlAndQr يتعمل الأول،
     * ولازم الشركة تكون خلصت المرحلة 1/2 من الـ ZATCA Onboarding (عندها certificate).
     */
    public function signInvoice(Invoice $invoice): Invoice
    {
        $result = $this->signingService->sign($invoice);

        $invoice->update([
            'xml_path' => $result['xml_path'],
            'invoice_hash' => $result['invoice_hash'],
        ]);

        return $invoice->fresh();
    }
}
