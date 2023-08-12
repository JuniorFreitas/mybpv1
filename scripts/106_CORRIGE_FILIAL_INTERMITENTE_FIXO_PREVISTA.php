<?php

use App\Models\VagasAbertas;
use App\Models\Vaga;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$kernel->terminate($request, $response);

ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

$intermitentes = DB::select("SELECT * FROM intermitente_fixo_previstas WHERE empresa_id = 40568");

foreach ($intermitentes as $linha){
    try {
        $dados_update = [
            'filial' => false,
            'centro_custo_filial_id' => null
        ];

        $upd_intermitente = DB::table('intermitente_fixo_previstas')
            ->where('id', $linha->id)
            ->update($dados_update);

        if($upd_intermitente){
            echo "ID : " . $linha->id . " | FILIAL ATUALIZADA EM INTERMITENTE FIXO\n";
        }
    }catch (Exception $exception) {
        echo $exception->getMessage();
    }
}

