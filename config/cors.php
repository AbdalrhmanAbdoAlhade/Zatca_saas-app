<?php

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    | حط هنا دومينز الـ React/Next.js بتاعتك (development + production).
    | الموبايل مش محتاج CORS أصلاً لأنه مش شغال جوه متصفح.
    */
    'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000,http://localhost:5173'))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false, // مش محتاجينها لأننا Bearer Token مش Cookie-based

];
