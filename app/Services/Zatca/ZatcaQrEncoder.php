<?php

namespace App\Services\Zatca;

/**
 * ترميز TLV (Tag-Length-Value) لكود QR بتاع ZATCA.
 *
 * Tags 1-5 (إلزامية دايماً):
 *   1 = اسم البائع
 *   2 = الرقم الضريبي
 *   3 = التاريخ/الوقت (ISO 8601)
 *   4 = إجمالي الفاتورة شامل الضريبة
 *   5 = إجمالي الضريبة
 *
 * Tags 6-9 (إلزامية بعد التوقيع الرقمي - المرحلة 2):
 *   6 = هاش الفاتورة (Base64)
 *   7 = التوقيع الرقمي (Base64)
 *   8 = المفتاح العام (Base64)
 *   9 = توقيع CA على المفتاح العام (Base64)
 */
class ZatcaQrEncoder
{
    public function encode(array $fields): string
    {
        $binary = '';

        foreach ($fields as $tag => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $valueBytes = (string) $value;
            $binary .= chr((int) $tag).chr(strlen($valueBytes)).$valueBytes;
        }

        return base64_encode($binary);
    }

    /**
     * QR بالحقول التجارية بس (Tags 1-5) - يخدم قبل ما التوقيع الرقمي يتعمل.
     */
    public function encodeBasic(string $sellerName, string $vatNumber, string $timestamp, string $totalWithVat, string $vatTotal): string
    {
        return $this->encode([
            1 => $sellerName,
            2 => $vatNumber,
            3 => $timestamp,
            4 => $totalWithVat,
            5 => $vatTotal,
        ]);
    }

    /**
     * QR كامل (Tags 1-9) - يخدم بعد ما التوقيع الرقمي والشهادة يكونوا جاهزين.
     */
    public function encodeSigned(
        string $sellerName,
        string $vatNumber,
        string $timestamp,
        string $totalWithVat,
        string $vatTotal,
        string $invoiceHashBase64,
        string $signatureBase64,
        string $publicKeyBase64,
        ?string $caSignatureBase64 = null,
    ): string {
        return $this->encode([
            1 => $sellerName,
            2 => $vatNumber,
            3 => $timestamp,
            4 => $totalWithVat,
            5 => $vatTotal,
            6 => base64_decode($invoiceHashBase64),
            7 => base64_decode($signatureBase64),
            8 => base64_decode($publicKeyBase64),
            9 => $caSignatureBase64 ? base64_decode($caSignatureBase64) : null,
        ]);
    }
}
