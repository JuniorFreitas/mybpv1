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

unset($argv[0]);

$empresa_id = $argv[1];
$user_id = $argv[2];

$DB = DB::select("SELECT a.id as admissao_id, fp.id, fp.periodo_aquisitivo, fp.qnt_dias, fp.data_saida, fp.data_retorno, fp.periodo_aquisitivo_id, pa.label as periodo_aquisitivo_label
    FROM
    ferias_previstas fp
        INNER JOIN periodos_aquisitivos as pa on pa.id = fp.periodo_aquisitivo_id
        INNER JOIN users as u on fp.colaborador_id = u.id
        INNER JOIN feedback_curriculos as fc on u.id = fc.curriculo_id
        INNER JOIN admissoes a on a.id = fc.id
    WHERE
        fp.periodo_aquisitivo_id in (4,5) AND fp.empresa_id = $empresa_id
    ORDER BY fp.data_saida asc
");

$DB2 = DB::select("SELECT pa.id, pa.label FROM periodos_aquisitivos pa");
$periodo_aquisitivo = [];
foreach ($DB2 as $pa){
    $periodo_aquisitivo[$pa->id] = $pa->label;
}

$hoje = (new DataHora())->dataInsert();
$cont = 0;

foreach ($DB as $key => $linha) {
    $status = "";
    $ferias_adquiridas = [];
    $data_limite = '';
    $proximo_periodo = '';

    if($linha->data_saida <= $hoje && $linha->data_retorno >= $hoje){
        $status = 'gozando';
    }

    if($linha->data_retorno < $hoje){
        $status = 'gozada';
    }

    if($linha->data_saida > $hoje){
        $status = 'aguardando';
    }

    $data_limite = (new DataHora($linha->data_retorno))->addAno(1);

    $proximo_periodo = $linha->periodo_aquisitivo_id + 1;

    $ferias_adquiridas[$key] = [
        'admissao_id' => $linha->admissao_id,
        'periodo_gozado' => $linha->periodo_aquisitivo_label,
        'qnt_dias' => $linha->qnt_dias,
        'data_saida' => $linha->data_saida,
        'data_retorno' => $linha->data_retorno,
        'proximo_periodo' => $periodo_aquisitivo[$proximo_periodo],
        'data_limite' => (new DataHora($data_limite))->dataInsert(),
        'user_cadastrou_id' => $user_id,
        'created_at' => (new DataHora())->dataHoraInsert(),
        'status' => $status,
        'ferias_prevista_id' => $linha->id
    ];

    try {
        $cadastrado = DB::table('ferias_adquiridas')
                        ->where('admissao_id', $ferias_adquiridas[$key]['admissao_id'])
                        ->where('periodo_gozado', $ferias_adquiridas[$key]['periodo_gozado'])
                        ->where('data_saida', $ferias_adquiridas[$key]['data_saida'])
                        ->count();
        if($cadastrado == 0){
            $ferias = DB::table('ferias_adquiridas')->insert($ferias_adquiridas[$key]);
            echo "ID : " . $linha->id . " | " . $cont++ . " CADASTRADA\n";
        }else{
            echo "ID : " . $linha->id . " | " . $cont++ . " JÁ CADASTRADA\n";
        }
    } catch (Exception $exception) {
        echo $exception->getMessage();
    }
}
