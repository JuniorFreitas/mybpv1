<?php

$default = [
    'driver' => env('FILESYSTEM_DRIVER', 'local'),
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'endpoint' => env('AWS_ENDPOINT'),
    'visibility' => 'public',
];

return [

    'default' => env('FILESYSTEM_DRIVER', 'local'),
    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'disco-cloud' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-cloud') : 'arquivos/disco-cloud',
                'urlShow' => env('APP_URL') . '/publico/cloud/anexo',
                'urlDownload' => env('APP_URL') . '/publico/cloud/anexo',
                'urlThumb' => env('APP_URL') . '/publico/cloud/anexo',
                'urlDelete' => env('APP_URL') . '/publico/cloud/anexo',
            ]
        ),
         'disco-exportacao' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-exportacao') : 'arquivos/disco-exportacao',
            ]
        ),

        'disco-cliente' => array_merge($default,
            [
//                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-cliente') : 'arquivos/disco-cliente',
//                'urlShow' => env('APP_URL') . '/g/administracao/clientes/anexo',
//                'urlDownload' => env('APP_URL') . '/g/administracao/clientes/anexo',
//                'urlThumb' => env('APP_URL') . '/g/administracao/clientes/anexo',
                'urlDelete' => env('APP_URL') . '/g/administracao/clientes/anexo',

                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-cliente') : 'arquivos/disco-cliente',
                'urlShow' => env('FILESYSTEM_DRIVER') == 'local' ? env('APP_URL') . "/g/storage/anexo" : env('AWS_URL') . '/arquivos/disco-cliente',
                'urlDownload' => env('FILESYSTEM_DRIVER') == 'local' ? env('APP_URL') . "/g/storage/anexo" : env('AWS_URL') . '/arquivos/disco-cliente',
                'urlThumb' => env('FILESYSTEM_DRIVER') == 'local' ? env('APP_URL') . "/g/storage/anexo" : env('AWS_URL') . '/arquivos/disco-cliente',

                'visibility' => 'public',
            ]
        ),

        'disco-fornecedor' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-fornecedor') : 'arquivos/disco-fornecedor',
                'urlShow' => env('APP_URL') . '/g/administracao/fornecedor/anexo',
                'urlDownload' => env('APP_URL') . '/g/administracao/fornecedor/anexo',
                'urlThumb' => env('APP_URL') . '/g/administracao/fornecedor/anexo',
                'urlDelete' => env('APP_URL') . '/g/administracao/fornecedor/anexo',
                'visibility' => 'public',
            ]
        ),

        'disco-servicofornecedor' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-servicofornecedor') : 'arquivos/disco-servicofornecedor',
                'urlShow' => env('APP_URL') . '/g/fornecedor/servico/anexo',
                'urlDownload' => env('APP_URL') . '/g/fornecedor/servico/anexo',
                'urlThumb' => env('APP_URL') . '/g/fornecedor/servico/anexo',
                'urlDelete' => env('APP_URL') . '/g/fornecedor/servico/anexo',
                'visibility' => 'public',
            ]
        ),

        'disco-ocorrencia' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-ocorrencia') : 'arquivos/disco-ocorrencia',
                'urlShow' => env('APP_URL') . '/g/ocorrencia/anexo',
                'urlDownload' => env('APP_URL') . '/g/ocorrencia/anexo',
                'urlThumb' => env('APP_URL') . '/g/ocorrencia/anexo',
                'urlDelete' => env('APP_URL') . '/g/ocorrencia/anexo',
                'visibility' => 'public',
            ]
        ),

        'disco-fotocurriculo' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-fotocurriculo') : 'arquivos/disco-fotocurriculo',
                'urlShow' => env('APP_URL') . '/g/admissao/anexo',
                'urlDownload' => env('APP_URL') . '/g/admissao/anexo',
                'urlThumb' => env('APP_URL') . '/g/admissao/anexo',
                'urlDelete' => env('APP_URL') . '/g/admissao/anexo',
            ]
        ),

        'documentos-funcionarios' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/documentos-funcionarios') : 'arquivos/documentos-funcionarios',
                'urlShow' => env('APP_URL') . '/g/funcionarios/anexo',
                'urlDownload' => env('APP_URL') . '/g/funcionarios/anexo',
                'urlThumb' => env('APP_URL') . '/g/funcionarios/anexo',
                'urlDelete' => env('APP_URL') . '/g/funcionarios/anexo',
                'visibility' => 'public',
            ]
        ),

        'evidencia-medidas' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/medidas-evidencia') : 'arquivos/medidas-evidencia',
                'urlShow' => env('APP_URL') . '/g/historico/medidas-administrativas/anexo',
                'urlDownload' => env('APP_URL') . '/g/historico/medidas-administrativas/anexo',
                'urlThumb' => env('APP_URL') . '/g/historico/medidas-administrativas/anexo',
                'urlDelete' => env('APP_URL') . '/g/historico/medidas-administrativas/anexo',
                'visibility' => 'public',
            ]
        ),

        'evidencia-cih' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/cih-evidencia') : 'arquivos/cih-evidencia',
                'urlShow' => env('APP_URL') . '/g/apontamento/cih/anexo',
                'urlDownload' => env('APP_URL') . '/g/apontamento/cih/anexo',
                'urlThumb' => env('APP_URL') . '/g/apontamento/cih/anexo',
                'urlDelete' => env('APP_URL') . '/g/apontamento/cih/anexo',
                'visibility' => 'public',
            ]
        ),

        'disco-documentospreadmissao' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-documentospreadmissao') : 'arquivos/disco-documentospreadmissao',
                'urlShow' => env('APP_URL') . '/documentos/anexo',
                'urlDownload' => env('APP_URL') . '/documentos/anexoDownload',
                'urlThumb' => env('APP_URL') . '/documentos/anexo',
                'urlDelete' => env('APP_URL') . '/documentos/anexo',
                'visibility' => 'public',
            ]
        ),

        'disco-dossie' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-dossie') : 'arquivos/disco-dossie',
                'urlShow' => env('APP_URL') . '/g/historico/dossie/anexo',
                'urlDownload' => env('APP_URL') . '/g/historico/dossie/anexo',
                'urlThumb' => env('APP_URL') . '/g/historico/dossie/anexo',
                'urlDelete' => env('APP_URL') . '/g/historico/dossie/anexo',
                'visibility' => 'public',
            ]
        ),

        'disco-perfil-usuario' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-perfil-usuario') : 'arquivos/disco-perfil-usuario',
                'urlShow' => env('APP_URL') . '/g/perfil/anexo',
                'urlDownload' => env('APP_URL') . '/g/perfil/anexo',
                'urlThumb' => env('APP_URL') . '/g/perfil/anexo',
                'urlDelete' => env('APP_URL') . '/g/perfil/anexo',
                'visibility' => 'public',
            ]
        ),

        'disco-weekly-report' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-weekly_report') : 'arquivos/disco-weekly_report',
                'urlShow' => env('APP_URL') . '/g/weekly-report/anexo',
                'urlDownload' => env('APP_URL') . '/g/weekly-report/anexo',
                'urlThumb' => env('APP_URL') . '/g/weekly-report/anexo',
                'urlDelete' => env('APP_URL') . '/g/weekly-report/anexo',
                'visibility' => 'public'
            ]
        ),

        'requisicao-vaga' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/requisicao-vaga') : 'arquivos/requisicao-vaga',
                'urlShow' => env('APP_URL') . '/g/planejamento/requisicao-vaga/anexo',
                'urlDownload' => env('APP_URL') . '/g/planejamento/requisicao-vaga/anexo',
                'urlThumb' => env('APP_URL') . '/g/planejamento/requisicao-vaga/anexo',
                'urlDelete' => env('APP_URL') . '/g/planejamento/requisicao-vaga/anexo',
                'visibility' => 'public',
            ]
        ),

        'listapresenca' => array_merge($default,
            [
                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/lista-presenca') : 'arquivos/lista-presenca',
                'urlShow' => env('APP_URL') . '/g/treinamento/listapresenca/anexo',
                'urlDownload' => env('APP_URL') . '/g/treinamento/listapresenca/anexo',
                'urlThumb' => env('APP_URL') . '/g/treinamento/listapresenca/anexo',
                'urlDelete' => env('APP_URL') . '/g/treinamento/listapresenca/anexo',
                'visibility' => 'public',
            ]
        ),

        'disco-ponto-eletronico' => array_merge($default,
            [
//                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-ponto-eletronico') : 'arquivos/disco-ponto-eletronico',
//                'urlShow' => env('APP_URL') . '/g/storage/anexo',
//                'urlDownload' => env('APP_URL') . '/g/storage/anexo',
//                'urlThumb' => env('APP_URL') . '/g/storage/anexo',
//                'urlDelete' => env('APP_URL') . '/g/storage/anexo',
//                'visibility' => 'public',

                'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-ponto-eletronico') : 'arquivos/disco-ponto-eletronico',
                'urlShow' => env('FILESYSTEM_DRIVER') == 'local' ? env('APP_URL') . "/g/storage/anexo" : env('AWS_URL') . '/arquivos/disco-ponto-eletronico',
                'urlDownload' => env('FILESYSTEM_DRIVER') == 'local' ? env('APP_URL') . "/g/storage/anexo" : env('AWS_URL') . '/arquivos/disco-ponto-eletronico',
                'urlThumb' => env('FILESYSTEM_DRIVER') == 'local' ? env('APP_URL') . "/g/storage/anexo" : env('AWS_URL') . '/arquivos/disco-ponto-eletronico',
                'urlDelete' => env('APP_URL') . '/g/storage/anexo',
                'visibility' => 'public',
            ]
        ),

        'public' => array_merge($default,
            [
                'root' => storage_path('app/public'),
                'urlShow' => env('APP_URL') . '/g/storage/anexo',
                'urlDownload' => env('APP_URL') . '/g/storage/anexo',
                'urlThumb' => env('APP_URL') . '/g/storage/anexo',
                'urlDelete' => env('APP_URL') . '/g/storage/anexo',
            ]
        ),

        's3' => array_merge($default,
            [
                'root' => storage_path('app/g'),
                'urlShow' => env('APP_URL') . '/g/storage/anexo',
                'urlDownload' => env('APP_URL') . '/g/storage/anexo',
                'urlThumb' => env('APP_URL') . '/g/storage/anexo',
                'urlDelete' => env('APP_URL') . '/g/storage/anexo',
                'visibility' => 'public',
            ]
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
