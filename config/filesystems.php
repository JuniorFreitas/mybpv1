<?php



return [

    'default' => env('FILESYSTEM_DRIVER', 'local'),
    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'disco-cloud' => [

            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-cloud') : 'arquivos/disco-cloud',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),



            'urlShow' => env('APP_URL') . '/publico/cloud/anexo',
            'urlDownload' => env('APP_URL') . '/publico/cloud/anexo',
            'urlThumb' => env('APP_URL') . '/publico/cloud/anexo',
            'urlDelete' => env('APP_URL') . '/publico/cloud/anexo',
            'visibility' => 'public',
        ],

        'disco-cliente' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-cliente') : 'arquivos/disco-cliente',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),


            'urlShow' => env('APP_URL') . '/g/storage/anexo',
            'urlDownload' => env('APP_URL') . '/g/storage/anexo',
            'urlThumb' => env('APP_URL') . '/g/storage/anexo',
            'urlDelete' => env('APP_URL') . '/g/storage/anexo',
            'visibility' => 'public',
        ],

        'disco-fornecedor' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-fornecedor') : 'arquivos/disco-fornecedor',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),


            'urlShow' => env('APP_URL') . '/g/administracao/fornecedor/anexo',
            'urlDownload' => env('APP_URL') . '/g/administracao/fornecedor/anexo',
            'urlThumb' => env('APP_URL') . '/g/administracao/fornecedor/anexo',
            'urlDelete' => env('APP_URL') . '/g/administracao/fornecedor/anexo',
            'visibility' => 'public',
        ],

        'disco-servicofornecedor' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-servicofornecedor') : 'arquivos/disco-servicofornecedor',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),


            'urlShow' => env('APP_URL') . '/g/fornecedor/servico/anexo',
            'urlDownload' => env('APP_URL') . '/g/fornecedor/servico/anexo',
            'urlThumb' => env('APP_URL') . '/g/fornecedor/servico/anexo',
            'urlDelete' => env('APP_URL') . '/g/fornecedor/servico/anexo',
            'visibility' => 'public',
        ],

        'disco-ocorrencia' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-ocorrencia') : 'arquivos/disco-ocorrencia',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),


            'urlShow' => env('APP_URL') . '/g/ocorrencia/anexo',
            'urlDownload' => env('APP_URL') . '/g/ocorrencia/anexo',
            'urlThumb' => env('APP_URL') . '/g/ocorrencia/anexo',
            'urlDelete' => env('APP_URL') . '/g/ocorrencia/anexo',
            'visibility' => 'public',
        ],

        'disco-fotocurriculo' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-fotocurriculo') : 'arquivos/disco-fotocurriculo',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),


            'urlShow' => env('APP_URL') . '/g/admissao/anexo',
            'urlDownload' => env('APP_URL') . '/g/admissao/anexo',
            'urlThumb' => env('APP_URL') . '/g/admissao/anexo',
            'urlDelete' => env('APP_URL') . '/g/admissao/anexo',
            'visibility' => 'public',
        ],

        'documentos-funcionarios' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/documentos-funcionarios') : 'arquivos/documentos-funcionarios',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),


            'urlShow' => env('APP_URL') . '/g/funcionarios/anexo',
            'urlDownload' => env('APP_URL') . '/g/funcionarios/anexo',
            'urlThumb' => env('APP_URL') . '/g/funcionarios/anexo',
            'urlDelete' => env('APP_URL') . '/g/funcionarios/anexo',
            'visibility' => 'public',
        ],

        'evidencia-medidas' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/medidas-evidencia') : 'arquivos/medidas-evidencia',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),


            'urlShow' => env('APP_URL') . '/g/historico/medidas-administrativas/anexo',
            'urlDownload' => env('APP_URL') . '/g/historico/medidas-administrativas/anexo',
            'urlThumb' => env('APP_URL') . '/g/historico/medidas-administrativas/anexo',
            'urlDelete' => env('APP_URL') . '/g/historico/medidas-administrativas/anexo',
            'visibility' => 'public',
        ],

        'evidencia-cih' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/cih-evidencia') : 'arquivos/cih-evidencia',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),


            'urlShow' => env('APP_URL') . '/g/apontamento/cih/anexo',
            'urlDownload' => env('APP_URL') . '/g/apontamento/cih/anexo',
            'urlThumb' => env('APP_URL') . '/g/apontamento/cih/anexo',
            'urlDelete' => env('APP_URL') . '/g/apontamento/cih/anexo',
            'visibility' => 'public',
        ],

        'disco-documentospreadmissao' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-documentospreadmissao') : 'arquivos/disco-documentospreadmissao',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),


            'urlShow' => env('APP_URL') . '/g/documentos/anexo',
            'urlDownload' => env('APP_URL') . '/g/documentos/anexo',
            'urlThumb' => env('APP_URL') . '/g/documentos/anexo',
            'urlDelete' => env('APP_URL') . '/g/documentos/anexo',
            'visibility' => 'public',

        ],
        'disco-dossie' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-dossie') : 'arquivos/disco-dossie',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),


            'urlShow' => env('APP_URL') . '/g/historico/dossie/anexo',
            'urlDownload' => env('APP_URL') . '/g/historico/dossie/anexo',
            'urlThumb' => env('APP_URL') . '/g/historico/dossie/anexo',
            'urlDelete' => env('APP_URL') . '/g/historico/dossie/anexo',
            'visibility' => 'public',

        ],
        'disco-perfil-usuario' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-perfil-usuario') : 'arquivos/disco-perfil-usuario',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),
//

            'urlShow' => env('APP_URL') . '/g/perfil/anexo',
            'urlDownload' => env('APP_URL') . '/g/perfil/anexo',
            'urlThumb' => env('APP_URL') . '/g/perfil/anexo',
            'urlDelete' => env('APP_URL') . '/g/perfil/anexo',
            'visibility' => 'public',

        ],

        'disco-weekly-report' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/disco-weekly_report') : 'arquivos/disco-weekly_report',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),
//
            'urlShow' => env('APP_URL').'/g/weekly-report/anexo',
            'urlDownload' => env('APP_URL').'/g/weekly-report/anexo',
            'urlThumb' => env('APP_URL').'/g/weekly-report/anexo',
            'urlDelete' => env('APP_URL').'/g/weekly-report/anexo',
            'visibility' => 'public',

//            'urlShow' => env('APP_URL') . '/g/perfil/anexo',
//            'urlDownload' => env('APP_URL') . '/g/perfil/anexo',
//            'urlThumb' => env('APP_URL') . '/g/perfil/anexo',
//            'urlDelete' => env('APP_URL') . '/g/perfil/anexo',
//            'visibility' => 'public',

        ],

        'requisicao-vaga' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/requisicao-vaga') : 'arquivos/requisicao-vaga',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),

            'urlShow' => env('APP_URL') . '/g/planejamento/requisicao-vaga/anexo',
            'urlDownload' => env('APP_URL') . '/g/planejamento/requisicao-vaga/anexo',
            'urlThumb' => env('APP_URL') . '/g/planejamento/requisicao-vaga/anexo',
            'urlDelete' => env('APP_URL') . '/g/planejamento/requisicao-vaga/anexo',
            'visibility' => 'public',

        ],

        'listapresenca' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER') == 'local' ? storage_path('app/g/arquivos/lista-presenca') : 'arquivos/lista-presenca',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),

            'urlShow' => env('APP_URL') . '/g/treinamento/listapresenca/anexo',
            'urlDownload' => env('APP_URL') . '/g/treinamento/listapresenca/anexo',
            'urlThumb' => env('APP_URL') . '/g/treinamento/listapresenca/anexo',
            'urlDelete' => env('APP_URL') . '/g/treinamento/listapresenca/anexo',
            'visibility' => 'public',

        ],

        'disco-ponto-eletronico' => [
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DRIVER')=='local' ? storage_path('app/g/arquivos/disco-ponto-eletronico'):'arquivos/disco-ponto-eletronico',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),


            'urlShow' => env('APP_URL') . '/g/storage/anexo',
            'urlDownload' => env('APP_URL') . '/g/storage/anexo',
            'urlThumb' => env('APP_URL') . '/g/storage/anexo',
            'urlDelete' => env('APP_URL') . '/g/storage/anexo',
            'visibility' => 'public',

        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),

            'urlShow' => env('APP_URL') . '/g/storage/anexo',
            'urlDownload' => env('APP_URL') . '/g/storage/anexo',
            'urlThumb' => env('APP_URL') . '/g/storage/anexo',
            'urlDelete' => env('APP_URL') . '/g/storage/anexo',
            'visibility' => 'public',
        ],

        's3' => [
            //'driver' => 's3',
            'driver' => env('FILESYSTEM_DRIVER', 'local'),
            'root' => storage_path('app/g'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),



            'urlShow' => env('APP_URL').'/g/storage/anexo',
            'urlDownload' => env('APP_URL').'/g/storage/anexo',
            'urlThumb' => env('APP_URL').'/g/storage/anexo',
            'urlDelete' => env('APP_URL').'/g/storage/anexo',
            'visibility' => 'public',
        ],



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
