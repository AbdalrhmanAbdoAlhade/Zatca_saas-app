<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ZATCA API Base URLs
    |--------------------------------------------------------------------------
    | مصدرها: بوابة المطورين الرسمية لهيئة الزكاة والضريبة والجمارك.
    | تأكد من مطابقتها لآخر نسخة موثقة وقت التشغيل لأنها بتتغير أحياناً.
    */
    'base_urls' => [
        'sandbox' => env('ZATCA_SANDBOX_URL', 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal'),
        'simulation' => env('ZATCA_SIMULATION_URL', 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation'),
        'production' => env('ZATCA_PRODUCTION_URL', 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core'),
    ],

    'api_version' => env('ZATCA_API_VERSION', 'V2'),

    /*
    |--------------------------------------------------------------------------
    | CSR Config Template Path
    |--------------------------------------------------------------------------
    | لازم تحط هنا مسار ملف الـ openssl config الرسمي بتاع ZATCA
    | (بيجي جوه الـ SDK الرسمي - عادة اسمه حاجة زي csr_config_template.cnf).
    | من غيره ZatcaCsrGenerator هيرفض يشتغل بدل ما يطلع شهادة غلط.
    */
    'csr_config_path' => env('ZATCA_CSR_CONFIG_PATH', storage_path('zatca/csr_config_template.cnf')),

    /*
    |--------------------------------------------------------------------------
    | EGS (Electronic Generation Solution) Defaults
    |--------------------------------------------------------------------------
    | بيانات وحدة الفوترة - ممكن تتخصص لكل شركة بدل القيم الافتراضية دي.
    */
    'egs' => [
        'model' => env('ZATCA_EGS_MODEL', 'Laravel-ZATCA-SaaS'),
        'solution_name' => env('ZATCA_SOLUTION_NAME', 'Laravel ZATCA SaaS Platform'),
        'vat_name' => env('ZATCA_VAT_NAME', 'Value Added Tax'),
        'country_code' => 'SA',
    ],
];
