<?php

$origins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
)));

// O SGI local chama a API direto do navegador (127.0.0.1:8885 → :8000).
if (env('APP_ENV') === 'local') {
    $origins = array_values(array_unique(array_merge($origins, [
        'http://127.0.0.1:8885',
        'http://localhost:8885',
    ])));
}

// Sem origins explícitos: não usar credentials com wildcard (spec CORS).
$supportsCredentials = $origins !== [] && (bool) env('CORS_SUPPORTS_CREDENTIALS', true);

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => $origins !== [] ? $origins : [],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'X-API-TOKEN', 'X-CSRF-TOKEN', 'Authorization', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 600,

    'supports_credentials' => $supportsCredentials,

];
