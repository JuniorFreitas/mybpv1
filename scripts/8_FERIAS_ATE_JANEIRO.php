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

//$empresa_id = $argv[1];
//$user_id = $argv[2];

$DB = DB::select("SELECT fp.id as empresa_id,
       a.id as admissao_id,
       a.data_admissao as data_admissao,
       fp.id,
       fp.periodo_aquisitivo_id,
       fp.empresa_id as empresa_id,
       fp.data_saida,
       fp.data_retorno,
       fp.ultima_data,
       fp.periodo_aquisitivo_id as periodo_aquisitivo_id,
       pa.ano_final as pa_ano_final,
       fp.qnt_dias,
       fp.dias_saldo,
       fp.tem_faltas,
       fp.qnt_faltas,
       fp.user_id as solicitante_id,
       fp.obs as obs_solicitante,
       fp.created_at as data_solicitacao,
       fp.gestor_id as gestor_aprovacao_id,
       fp.obs_aprovacao as obs_gestor,
       fp.status_aprovacao as status_aprovacao_gestor,
       fp.data_aprovacao as data_aprovacao_gestor,
       fp.user_rh_id as rh_aprovacao_id,
       fp.resposta_rh as status_aprovacao_rh,
       fp.obs_rh,
       fp.data_aprovacao_rh
    FROM
    ferias_previstas fp
        INNER JOIN periodos_aquisitivos as pa on pa.id = fp.periodo_aquisitivo_id
        INNER JOIN users as u on fp.colaborador_id = u.id
        INNER JOIN feedback_curriculos as fc on u.id = fc.curriculo_id
        INNER JOIN admissoes a on a.id = fc.id
    WHERE
        a.data_admissao <= '2022-01-31'
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

    if($linha->status_aprovacao_gestor == 'aprovado'){
        if($linha->data_saida <= $hoje && $linha->data_retorno >= $hoje){
            $status = 'gozando';
        }

        if($linha->data_retorno < $hoje){
            $status = 'gozada';
        }

        if($linha->data_saida > $hoje){
            $status = 'aguardando';
        }
    }

//    $proximo_periodo = $linha->periodo_aquisitivo_id + 1;
//
//    $date = new DataHora($linha->data_admissao);
//    $ultimoAnoPeriodoAquisitivo = $linha->pa_ano_final . '-' . $date->mes() . '-' . $date->dia();
//    $newDate = new DataHora($ultimoAnoPeriodoAquisitivo);
//    $newDate->addDia(330);
//    $data_limite = $newDate->dataInsert();

    $ferias[$key] = [
        'empresa_id' => $linha->empresa_id,
        'admissao_id' => $linha->admissao_id,
        'periodo_aquisitivo_id' => $linha->periodo_aquisitivo_id,
        'data_saida' => $linha->data_saida,
        'data_retorno' => $linha->data_retorno,
        'ultima_data' => $linha->ultima_data,
        'qnt_dias' => $linha->qnt_dias,
        'dias_saldo' => $linha->dias_saldo,
        'tem_faltas' => $linha->tem_faltas,
        'qnt_faltas' => $linha->qnt_faltas,
        'solicitante_id' => $linha->solicitante_id,
        'obs_solicitante' => $linha->obs_solicitante,
        'data_solicitacao' => $linha->data_solicitacao,
        'data_solicitacao' => $linha->data_solicitacao,
        'gestor_aprovacao_id' => $linha->gestor_aprovacao_id,
        'obs_gestor' => $linha->obs_gestor,
        'status_aprovacao_gestor' => $linha->status_aprovacao_gestor,
        'gestor_aprovacao_id' => $linha->gestor_aprovacao_id,
        'data_aprovacao_gestor' => $linha->data_aprovacao_gestor,
        'rh_aprovacao_id' => $linha->rh_aprovacao_id,
        'obs_rh' => $linha->obs_rh,
        'status_aprovacao_rh' => $linha->status_aprovacao_rh,
        'data_aprovacao_rh' => $linha->data_aprovacao_rh,
        'status_ferias' => $status,
        'ferias_prevista_id' => $linha->id,
        'aprovado_via_script' => true
    ];

    try {
        $cadastrado = DB::table('ferias')
                        ->where('admissao_id', $ferias[$key]['admissao_id'])
                        ->where('periodo_aquisitivo_id', $ferias[$key]['periodo_aquisitivo_id'])
                        ->where('data_saida', $ferias[$key]['data_saida'])
                        ->count();
        if($cadastrado == 0){
            $cadFerias = DB::table('ferias')->insert($ferias[$key]);
            echo "ID : " . $linha->id . " | " . $cont++ . " CADASTRADA\n";
        }else{
            echo "ID : " . $linha->id . " | " . $cont++ . " JÁ CADASTRADA\n";
        }
    } catch (Exception $exception) {
        echo $exception->getMessage();
    }
}
