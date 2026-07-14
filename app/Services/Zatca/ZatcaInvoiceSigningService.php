<?php

namespace App\Services\Zatca;

use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Saleh7\Zatca\GeneratorInvoice;
use Saleh7\Zatca\Helpers\Certificate;
use Saleh7\Zatca\InvoiceSigner;
use Saleh7\Zatca\Mappers\InvoiceMapper;

/**
 * التوقيع الرقمي الفعلي للفاتورة (XAdES-BES) باستخدام مكتبة saleh7/php-zatca-xml
 * بدل تنفيذ الـ canonicalization يدوياً - راجع تحذيرات ZatcaInvoiceXmlBuilder ليه.
 *
 * لازم تركّب المكتبة الأول:
 *   composer require saleh7/php-zatca-xml
 *
 * ⚠️ الأسماء الدقيقة لبعض methods (getInvoice/getXML، إلخ) اتغيرت بين نسخ
 * المكتبة المختلفة (موثق في الـ Releases بتاعتها). راجع الـ README الخاص
 * بالنسخة المثبتة عندك فعلياً قبل ما تعتمد على الأسماء هنا 100%.
 */
class ZatcaInvoiceSigningService
{
    public function sign(Invoice $invoice): array
    {
        if (! class_exists(GeneratorInvoice::class)) {
            throw new RuntimeException('saleh7_zatca_package_not_installed');
        }

        $invoice->loadMissing('items', 'customer', 'company');
        $zatcaSettings = $invoice->company->zatcaSettings;

        if (! $zatcaSettings || ! $zatcaSettings->certificate || ! $zatcaSettings->private_key) {
            throw new RuntimeException('company_certificate_not_ready');
        }

        $invoiceArray = $this->mapInvoiceToLibraryFormat($invoice);

        $mapper = new InvoiceMapper();
        $mappedInvoice = $mapper->mapToInvoice($invoiceArray);

        $generator = new GeneratorInvoice();
        $xml = $generator->invoice($mappedInvoice)->getXML();

        // secret_key هنا هو الـ passphrase بتاع الـ private key لو موجود (غالباً فاضي مع مفاتيح ZATCA)
        $certificate = new Certificate(
            $zatcaSettings->certificate,
            $zatcaSettings->private_key,
            $zatcaSettings->secret_key ?? '',
        );

        $signer = InvoiceSigner::signInvoice($xml, $certificate);

        $signedXml = method_exists($signer, 'getInvoice') ? $signer->getInvoice() : $signer->getXML();
        $hash = $signer->getHash();

        $xmlPath = "zatca-invoices/{$invoice->company_id}/{$invoice->uuid}-signed.xml";
        Storage::disk('local')->put($xmlPath, $signedXml);

        return [
            'signed_xml' => $signedXml,
            'xml_path' => $xmlPath,
            'invoice_hash' => $hash,
        ];
    }

    /**
     * تحويل بيانات الفاتورة من الموديل بتاعنا للصيغة اللي InvoiceMapper بتاع
     * المكتبة محتاجها. راجع أمثلة الـ README لو حقول زيادة اتضافت في نسخة أحدث.
     */
    protected function mapInvoiceToLibraryFormat(Invoice $invoice): array
    {
        $company = $invoice->company;
        $isSimplified = $invoice->invoice_type === 'simplified_tax_invoice';

        return [
            'uuid' => $invoice->uuid,
            'id' => $invoice->invoice_number,
            'issueDate' => $invoice->issue_date->format('Y-m-d H:i:s'),
            'issueTime' => $invoice->created_at?->format('H:i:s') ?? '00:00:00',
            'currencyCode' => $invoice->currency,
            'taxCurrencyCode' => $invoice->currency,
            'invoiceType' => [
                'invoice' => $isSimplified ? 'simplified' : 'standard',
                'type' => match ($invoice->invoice_type) {
                    'credit_note' => 'credit',
                    'debit_note' => 'debit',
                    default => 'invoice',
                },
                'isThirdParty' => false,
                'isNominal' => false,
                'isExport' => false,
                'isSummary' => false,
                'isSelfBilled' => false,
            ],
            'additionalDocuments' => [
                ['id' => 'ICV', 'uuid' => (string) $invoice->icv],
                [
                    'id' => 'PIH',
                    'attachment' => [
                        // 'MA==' هي base64('0') وده اللي ZATCA بتطلبه لأول فاتورة في السلسلة
                        'content' => $invoice->previous_invoice_hash
                            ? base64_encode($invoice->previous_invoice_hash)
                            : 'MA==',
                    ],
                ],
            ],
            'supplier' => [
                'registrationName' => $company->trade_name_en ?? $company->trade_name_ar,
                'taxId' => $company->vat_number,
                'identificationId' => $company->commercial_registration_number,
                'identificationType' => 'CRN',
                'address' => [
                    'street' => $company->street,
                    'buildingNumber' => $company->building_number,
                    'subdivision' => $company->district,
                    'city' => $company->city,
                    'postalZone' => $company->postal_code,
                    'country' => $company->country ?? 'SA',
                ],
            ],
            'buyer' => $invoice->customer ? [
                'registrationName' => $invoice->customer->name_en ?? $invoice->customer->name_ar,
                'taxId' => $invoice->customer->vat_number,
                'address' => [
                    'street' => $invoice->customer->street,
                    'buildingNumber' => $invoice->customer->building_number,
                    'city' => $invoice->customer->city,
                    'postalZone' => $invoice->customer->postal_code,
                    'country' => $invoice->customer->country ?? 'SA',
                ],
            ] : null,
            'lines' => $invoice->items->map(fn ($item) => [
                'name' => $item->name_en ?? $item->name_ar,
                'quantity' => (float) $item->quantity,
                'price' => (float) $item->unit_price,
                'taxPercent' => (float) $item->tax_percentage,
                'discount' => (float) $item->discount_amount,
            ])->toArray(),
        ];
    }
}
