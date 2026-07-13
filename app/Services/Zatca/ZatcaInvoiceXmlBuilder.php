<?php

namespace App\Services\Zatca;

use App\Models\Invoice;
use DOMDocument;
use DOMElement;

/**
 * بناء XML بصيغة UBL 2.1 للفاتورة، يغطي الحقول التجارية الأساسية المطلوبة من ZATCA.
 *
 * ملحوظة مهمة: الملف ده بيطلع XML "غير موقّع" (unsigned). التوقيع الرقمي
 * (XAdES enveloped signature + QR tags 6-9) خطوة منفصلة ومحتاجة مكتبة
 * متخصصة للـ canonicalization الصحيح - راجع الشرح في ZatcaQrEncoder.
 *
 * راجع كمان قواعد العمل (Business Rules BR-KSA-*) والـ XSD الرسمي قبل
 * الاعتماد على المخرجات دي في production - الملف ده بيغطي الهيكل العام
 * مش كل حالة خاصة (زي الخصومات على مستوى الفاتورة، أو رموز الإعفاء الضريبي).
 */
class ZatcaInvoiceXmlBuilder
{
    protected const NS_INVOICE = 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2';
    protected const NS_CAC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
    protected const NS_CBC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';
    protected const NS_EXT = 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2';

    public function build(Invoice $invoice, array $companyData): string
    {
        $invoice->loadMissing('items', 'customer', 'supplier');

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElementNS(self::NS_INVOICE, 'Invoice');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cac', self::NS_CAC);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cbc', self::NS_CBC);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ext', self::NS_EXT);
        $dom->appendChild($root);

        $this->text($dom, $root, 'cbc:ProfileID', 'reporting:1.0');
        $this->text($dom, $root, 'cbc:ID', $invoice->invoice_number);
        $this->text($dom, $root, 'cbc:UUID', $invoice->uuid);
        $this->text($dom, $root, 'cbc:IssueDate', $invoice->issue_date->format('Y-m-d'));
        $this->text($dom, $root, 'cbc:IssueTime', $invoice->created_at?->format('H:i:s') ?? '00:00:00');

        $typeCode = $root->appendChild($dom->createElementNS(self::NS_CBC, 'cbc:InvoiceTypeCode', $this->invoiceTypeCode($invoice->invoice_type)));
        $typeCode->setAttribute('name', $this->invoiceTypeName($invoice->invoice_type));

        $this->text($dom, $root, 'cbc:DocumentCurrencyCode', $invoice->currency);
        $this->text($dom, $root, 'cbc:TaxCurrencyCode', $invoice->currency);

        // مرجع الـ ICV والـ PIH (الهاش السابق) - إلزاميين من ZATCA
        $icvRef = $dom->createElementNS(self::NS_CAC, 'cac:AdditionalDocumentReference');
        $this->text($dom, $icvRef, 'cbc:ID', 'ICV');
        $this->text($dom, $icvRef, 'cbc:UUID', (string) $invoice->icv);
        $root->appendChild($icvRef);

        $pihRef = $dom->createElementNS(self::NS_CAC, 'cac:AdditionalDocumentReference');
        $this->text($dom, $pihRef, 'cbc:ID', 'PIH');
        $attachment = $dom->createElementNS(self::NS_CAC, 'cac:Attachment');
        $embedded = $dom->createElementNS(self::NS_CBC, 'cbc:EmbeddedDocumentBinaryObject', base64_encode($invoice->previous_invoice_hash ?? ''));
        $embedded->setAttribute('mimeCode', 'text/plain');
        $attachment->appendChild($embedded);
        $pihRef->appendChild($attachment);
        $root->appendChild($pihRef);

        // البائع (الشركة)
        $root->appendChild($this->buildParty($dom, 'cac:AccountingSupplierParty', [
            'name' => $companyData['trade_name_ar'],
            'vat_number' => $companyData['vat_number'],
            'street' => $companyData['street'] ?? null,
            'city' => $companyData['city'] ?? null,
            'building_number' => $companyData['building_number'] ?? null,
            'postal_code' => $companyData['postal_code'] ?? null,
            'country' => $companyData['country'] ?? 'SA',
        ]));

        // المشتري (العميل) - لو فاتورة B2B
        if ($invoice->customer) {
            $root->appendChild($this->buildParty($dom, 'cac:AccountingCustomerParty', [
                'name' => $invoice->customer->name_ar,
                'vat_number' => $invoice->customer->vat_number,
                'street' => $invoice->customer->street,
                'city' => $invoice->customer->city,
                'building_number' => $invoice->customer->building_number,
                'postal_code' => $invoice->customer->postal_code,
                'country' => $invoice->customer->country ?? 'SA',
            ]));
        }

        // إجمالي الضريبة
        $taxTotal = $dom->createElementNS(self::NS_CAC, 'cac:TaxTotal');
        $taxAmount = $dom->createElementNS(self::NS_CBC, 'cbc:TaxAmount', number_format((float) $invoice->tax_amount, 2, '.', ''));
        $taxAmount->setAttribute('currencyID', $invoice->currency);
        $taxTotal->appendChild($taxAmount);
        $root->appendChild($taxTotal);

        // الإجمالي النهائي
        $monetaryTotal = $dom->createElementNS(self::NS_CAC, 'cac:LegalMonetaryTotal');
        $this->amount($dom, $monetaryTotal, 'cbc:LineExtensionAmount', $invoice->subtotal, $invoice->currency);
        $this->amount($dom, $monetaryTotal, 'cbc:TaxExclusiveAmount', $invoice->subtotal - $invoice->discount_amount, $invoice->currency);
        $this->amount($dom, $monetaryTotal, 'cbc:TaxInclusiveAmount', $invoice->total_amount, $invoice->currency);
        $this->amount($dom, $monetaryTotal, 'cbc:AllowanceTotalAmount', $invoice->discount_amount, $invoice->currency);
        $this->amount($dom, $monetaryTotal, 'cbc:PayableAmount', $invoice->total_amount, $invoice->currency);
        $root->appendChild($monetaryTotal);

        // بنود الفاتورة
        foreach ($invoice->items as $index => $item) {
            $root->appendChild($this->buildInvoiceLine($dom, $index + 1, $item, $invoice->currency));
        }

        return $dom->saveXML();
    }

    protected function buildParty(DOMDocument $dom, string $wrapperTag, array $data): DOMElement
    {
        $wrapper = $dom->createElementNS(self::NS_CAC, $wrapperTag);
        $party = $dom->createElementNS(self::NS_CAC, 'cac:Party');

        if (! empty($data['vat_number'])) {
            $taxScheme = $dom->createElementNS(self::NS_CAC, 'cac:PartyTaxScheme');
            $this->text($dom, $taxScheme, 'cbc:CompanyID', $data['vat_number']);
            $scheme = $dom->createElementNS(self::NS_CAC, 'cac:TaxScheme');
            $this->text($dom, $scheme, 'cbc:ID', 'VAT');
            $taxScheme->appendChild($scheme);
            $party->appendChild($taxScheme);
        }

        $legalEntity = $dom->createElementNS(self::NS_CAC, 'cac:PartyLegalEntity');
        $this->text($dom, $legalEntity, 'cbc:RegistrationName', $data['name'] ?? '');
        $party->appendChild($legalEntity);

        $address = $dom->createElementNS(self::NS_CAC, 'cac:PostalAddress');
        $this->text($dom, $address, 'cbc:StreetName', $data['street'] ?? '');
        $this->text($dom, $address, 'cbc:BuildingNumber', $data['building_number'] ?? '');
        $this->text($dom, $address, 'cbc:CityName', $data['city'] ?? '');
        $this->text($dom, $address, 'cbc:PostalZone', $data['postal_code'] ?? '');
        $country = $dom->createElementNS(self::NS_CAC, 'cac:Country');
        $this->text($dom, $country, 'cbc:IdentificationCode', $data['country'] ?? 'SA');
        $address->appendChild($country);
        $party->appendChild($address);

        $wrapper->appendChild($party);

        return $wrapper;
    }

    protected function buildInvoiceLine(DOMDocument $dom, int $lineNumber, $item, string $currency): DOMElement
    {
        $line = $dom->createElementNS(self::NS_CAC, 'cac:InvoiceLine');
        $this->text($dom, $line, 'cbc:ID', (string) $lineNumber);

        $qty = $dom->createElementNS(self::NS_CBC, 'cbc:InvoicedQuantity', (string) $item->quantity);
        $qty->setAttribute('unitCode', 'PCE');
        $line->appendChild($qty);

        $this->amount($dom, $line, 'cbc:LineExtensionAmount', $item->quantity * $item->unit_price - $item->discount_amount, $currency);

        $taxTotal = $dom->createElementNS(self::NS_CAC, 'cac:TaxTotal');
        $this->amount($dom, $taxTotal, 'cbc:TaxAmount', $item->tax_amount, $currency);
        $line->appendChild($taxTotal);

        $itemEl = $dom->createElementNS(self::NS_CAC, 'cac:Item');
        $this->text($dom, $itemEl, 'cbc:Name', $item->name_ar);
        $taxCategory = $dom->createElementNS(self::NS_CAC, 'cac:ClassifiedTaxCategory');
        $this->text($dom, $taxCategory, 'cbc:Percent', number_format((float) $item->tax_percentage, 2, '.', ''));
        $scheme = $dom->createElementNS(self::NS_CAC, 'cac:TaxScheme');
        $this->text($dom, $scheme, 'cbc:ID', 'VAT');
        $taxCategory->appendChild($scheme);
        $itemEl->appendChild($taxCategory);
        $line->appendChild($itemEl);

        $price = $dom->createElementNS(self::NS_CAC, 'cac:Price');
        $this->amount($dom, $price, 'cbc:PriceAmount', $item->unit_price, $currency);
        $line->appendChild($price);

        return $line;
    }

    protected function text(DOMDocument $dom, DOMElement $parent, string $tag, string $value): DOMElement
    {
        $ns = str_starts_with($tag, 'cbc:') ? self::NS_CBC : self::NS_CAC;
        $el = $dom->createElementNS($ns, $tag, htmlspecialchars($value, ENT_XML1));
        $parent->appendChild($el);

        return $el;
    }

    protected function amount(DOMDocument $dom, DOMElement $parent, string $tag, float $value, string $currency): DOMElement
    {
        $el = $dom->createElementNS(self::NS_CBC, $tag, number_format($value, 2, '.', ''));
        $el->setAttribute('currencyID', $currency);
        $parent->appendChild($el);

        return $el;
    }

    protected function invoiceTypeCode(string $invoiceType): string
    {
        return match ($invoiceType) {
            'tax_invoice' => '388',
            'simplified_tax_invoice' => '388',
            'credit_note' => '381',
            'debit_note' => '383',
            default => '388',
        };
    }

    /**
     * الـ 7 أرقام دي (subtype) بتحدد نوع الفاتورة والخصائص المفعّلة
     * (Invoice/Simplified, مع/بدون Third Party, Nominal, Export, Summary, Self-billed).
     * القيم هنا افتراضية للحالة الأساسية - عدّلها حسب نشاط كل شركة.
     */
    protected function invoiceTypeName(string $invoiceType): string
    {
        return match ($invoiceType) {
            'simplified_tax_invoice' => '0200000',
            default => '0100000',
        };
    }
}
