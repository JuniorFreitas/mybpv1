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

$intermitentes = DB::select("SELECT * FROM intermitente_fixo_previstas");

foreach ($intermitentes as $linha){
    $empresa_id = $linha->empresa_id;
    $cargo_anterior_id = $linha->cargo_anterior_id;
    $novo_cargo_id = $linha->novo_cargo_id;
    $municipio_id = 2743;

    if($empresa_id === 63122) {
        continue;
    }

    try {
        $vaga_aberta_anterior = VagasAbertas::withoutGlobalScopes()
            ->where('vaga_id', $cargo_anterior_id)
            ->where('empresa_id', $empresa_id)
            ->first();

        $vaga_aberta_nova = VagasAbertas::withoutGlobalScopes()
            ->where('vaga_id', $novo_cargo_id)
            ->where('empresa_id', $empresa_id)
            ->first();

        $dados_update = [
            'anterior_vaga_aberta_id' => $vaga_aberta_anterior->id,
            'nova_vaga_aberta_id' => $vaga_aberta_nova->id,
        ];

        $upd_intermitente = DB::table('intermitente_fixo_previstas')
            ->where('id', $linha->id)
            ->whereNull('anterior_vaga_aberta_id')
            ->whereNull('nova_vaga_aberta_id')
            ->update($dados_update);

        if($upd_intermitente){
            echo "ID : " . $linha->id . " | VAGA ABERTA ATUALIZADA EM INTERMITENTE FIXO\n";
        }
    }catch (Exception $exception) {
        echo $exception->getMessage();
    }
}

