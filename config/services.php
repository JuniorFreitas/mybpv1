<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'cbo' => [
        'download_page' => env(
            'CBO_DOWNLOAD_PAGE',
            'https://www.gov.br/trabalho-e-emprego/pt-br/assuntos/cbo/servicos/downloads'
        ),
        'ocupacoes_csv_url' => env('CBO_OCUPACOES_CSV_URL'),
        'familias_csv_url' => env('CBO_FAMILIAS_CSV_URL'),
        'perfil_csv_url' => env('CBO_PERFIL_CSV_URL'),
        /** Arquivos menores que isso não são considerados cache válido (evita ficar preso a stub/teste). */
        'min_csv_bytes_for_cache' => (int) env('CBO_MIN_CSV_BYTES_FOR_CACHE', 2000),
    ],

];
