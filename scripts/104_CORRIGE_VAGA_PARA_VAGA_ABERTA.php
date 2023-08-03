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

$intermitentes = DB::select("SELECT * FROM vagas WHERE ativo = 1");

foreach ($intermitentes as $linha){
    $empresa_id = $linha->empresa_id;
    $municipio_id = 2743;
    $vaga_id = $linha->id;
    $titulo = $linha->nome;

    try {
       $corrige_vaga = firstOrCreateVagaAberta($vaga_id, $municipio_id, $empresa_id, $titulo);
        if ($corrige_vaga) {
            echo "ID : " . $linha->id . " | VAGA_ID : " . $corrige_vaga->id ." | VAGA ABERTA ATUALIZADA\n";
        }
    }catch (Exception $exception) {
        echo $exception->getMessage();
    }
}

function firstOrCreateVagaAberta($vaga_id, $municipio_id, $empresa_id, $titulo, $descricao = '', $ativo_sistema = true, $ativo = true)
{
    $vaga_aberta_exite = VagasAbertas::withoutGlobalScopes()
                                     ->where('vaga_id', $vaga_id)
                                     ->where('empresa_id', $empresa_id)
                                     ->first();

    if(!$vaga_aberta_exite){
        $vaga = VagasAbertas::withoutGlobalScopes()->create([
            'vaga_id' => $vaga_id,
            'municipio_id' => $municipio_id,
            'empresa_id' => $empresa_id,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'ativo_sistema' => $ativo_sistema,
            'ativo' => $ativo
        ]);
        return $vaga;
    }
    return false;
}

