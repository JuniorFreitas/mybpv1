<?php

use App\Models\Feriado;
use App\Models\OcorrenciaJornada;
use App\Models\PontoEletronico;
use App\Models\User;
use MasterTag\DataHora;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$kernel->terminate($request, $response);

ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

unset($argv[0]);

// Dados para o JSON
$headeres = [
    ["Nome", "Idade", "Cidade","Nome", "Idade", "Cidade","Nome", "Idade", "Cidade","Nome", "Idade", "Cidade","Nome", "Idade", "Cidade","Nome", "Idade", "Cidade","Nome", "Idade", "Cidade","Nome", "Idade", "Cidade","Nome", "Idade", "Cidade","Nome", "Idade", "Cidade","Nome", "Idade", "Cidade","Nome", "Idade", "Cidade","Nome", "Idade", "Cidade",],
];

$dados = [];
foreach (range(1, 2) as $item) {
    $dados[] = [
        "Nome $item",
        "Idade $item",
        "Cidade $item",
        "Nome $item",
        "Nome $item",
        "Idade $item",
        "Cidade $item",
        "Nome $item",
        "Nome $item",
        "Idade $item",
        "Cidade $item",
        "Nome $item",
        "Nome $item",
        "Idade $item",
        "Cidade $item",
        "Nome $item",
        "Nome $item",
        "Idade $item",
        "Cidade $item",
        "Nome $item",
        "Nome $item",
        "Idade $item",
        "Idade $item",
        "Cidade $item",
        "Cidade $item",
        "Nome $item",
        "Nome $item",
        "Cidade $item",
        "Cidade $item",
        "Nome $item",
        "Nome $item",
        "Nome $item",
        "Nome $item",
        "Idade $item",
        "Idade $item",
        "Cidade $item",
        "Cidade $item",
        "Nome $item",
        "Nome $item",
    ];
}


$arquivo = (new \MasterTag\DataHora())->nomeUnico();

$merge = array_merge($headeres,$dados);

//    Artisan::call("mybp:exportExcel {$merge} {$arquivo}");
Artisan::call("mybp:exportExcel", ['dados' => $merge, 'arquivo' => $arquivo]);
