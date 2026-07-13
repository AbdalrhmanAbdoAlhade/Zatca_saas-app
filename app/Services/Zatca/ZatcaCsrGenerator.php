<?php

namespace App\Services\Zatca;

use RuntimeException;

/**
 * توليد Private Key (EC/secp256k1) و CSR بالبنية اللي بتطلبها ZATCA.
 *
 * ملحوظة مهمة: البنية دي متطابقة مع عدة مصادر مستقلة (مكتبات مفتوحة المصدر
 * ودليل NetSuite الرسمي للتكامل مع ZATCA)، لكن برضو راجعها مقابل آخر نسخة
 * من SDK/Postman collection الرسمي بتاع ZATCA قبل الاعتماد عليها في production،
 * لأن أي تغيير بسيط في البنية ممكن يخلي الشهادة تترفض.
 */
class ZatcaCsrGenerator
{
    /**
     * @param  array{
     *     environment: string,
     *     organization_identifier: string,
     *     organization_unit_name: string,
     *     organization_name: string,
     *     common_name: string,
     *     egs_serial_number: string,
     *     invoice_type: string,
     *     location: string,
     *     business_category: string,
     * }  $data
     * @return array{private_key: string, csr: string}
     */
    public function generate(array $data): array
    {
        if (! extension_loaded('openssl')) {
            throw new RuntimeException('openssl_extension_not_loaded');
        }

        $configPath = $this->writeTempConfig($data);

        try {
            $privateKey = openssl_pkey_new([
                'private_key_type' => OPENSSL_KEYTYPE_EC,
                'curve_name' => 'secp256k1',
                'config' => $configPath,
            ]);

            if (! $privateKey) {
                throw new RuntimeException('failed_to_generate_private_key: '.openssl_error_string());
            }

            $dn = [
                'countryName' => 'SA',
                'organizationUnitName' => $data['organization_unit_name'],
                'organizationName' => $data['organization_name'],
                'commonName' => $data['common_name'],
            ];

            $csrResource = openssl_csr_new(
                $dn,
                $privateKey,
                [
                    'digest_alg' => 'sha256',
                    'req_extensions' => 'req_ext',
                    'config' => $configPath,
                ]
            );

            if (! $csrResource) {
                throw new RuntimeException('failed_to_generate_csr: '.openssl_error_string());
            }

            openssl_csr_export($csrResource, $csrOut);
            openssl_pkey_export($privateKey, $privateKeyOut, null, ['config' => $configPath]);

            if (! $csrOut || ! $privateKeyOut) {
                throw new RuntimeException('failed_to_export_csr_or_key');
            }

            return [
                'private_key' => $privateKeyOut,
                'csr' => $csrOut,
            ];
        } finally {
            @unlink($configPath);
        }
    }

    protected function writeTempConfig(array $data): string
    {
        $envPrefix = $data['environment'] === 'production' ? 'PRD' : 'TST';

        $config = <<<CNF
oid_section = OIDs

[ OIDs ]
certificateTemplateName = 1.3.6.1.4.1.311.20.2

[ req ]
default_bits = 256
prompt = no
default_md = sha256
req_extensions = req_ext
distinguished_name = dn

[ dn ]
C = SA
OU = {$this->escape($data['organization_unit_name'])}
O = {$this->escape($data['organization_name'])}
CN = {$envPrefix}-{$this->escape($data['common_name'])}

[ req_ext ]
basicConstraints = CA:FALSE
keyUsage = digitalSignature, nonRepudiation, keyEncipherment
certificateTemplateName = ASN1:PRINTABLESTRING:ZATCA-Code-Signing
subjectAltName = dirName:alt_names

[ alt_names ]
SN = {$this->escape($data['egs_serial_number'])}
UID = {$this->escape($data['organization_identifier'])}
title = {$this->escape($data['invoice_type'])}
registeredAddress = {$this->escape($data['location'])}
businessCategory = {$this->escape($data['business_category'])}
CNF;

        $path = tempnam(sys_get_temp_dir(), 'zatca_csr_').'.cnf';
        file_put_contents($path, $config);

        return $path;
    }

    protected function escape(string $value): string
    {
        return trim($value);
    }
}
