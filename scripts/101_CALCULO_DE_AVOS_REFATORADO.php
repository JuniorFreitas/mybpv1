<?php

use Illuminate\Support\Facades\DB;
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

//unset($argv[0]);

//if ($argv[1]) {
//    foreach (range(1996, 2017) as $n) {
//        DB::table('periodos_aquisitivos')->insert([
//            'label' => $n . '/' . ($n + 1),
//            'ano_inicial' => $n,
//            'ano_final' => ($n + 1),
//        ]);
//    }
//    echo "Periodos aquisitivos criados com sucesso!\n";
//    Log::info("Periodos aquisitivos criados com sucesso!");
//}

Artisan::call('mybp:calculoAvos');
