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

$admissoes = DB::select("SELECT
    f.id as ferias_id, a.id as admissao_id, u.nome, c.nome, a.feedback_id, a.data_admissao, fc.empresa_id, f.periodo_aquisitivo_id, f.status_ferias, f.qnt_dias,
    f.qnt_faltas, f.dias_saldo, f.data_saida, f.data_retorno, f.aprovado_via_script, f.status_aprovacao_gestor,
    f.gestor_aprovacao_id, f.deleted_at, g.nome as gestor_aprovacao_nome, pa.label as periodo_aquisitivo_label
FROM admissoes a
         INNER JOIN feedback_curriculos fc on a.feedback_id = fc.id
         LEFT JOIN ferias f on a.id = f.admissao_id
         Left join curriculos c on fc.curriculo_id = c.id
         left join users u on fc.empresa_id = u.id
         Inner join periodos_aquisitivos pa on f.periodo_aquisitivo_id = pa.id
         inner join users as g on f.gestor_aprovacao_id = g.id
WHERE fc.empresa_id = 39765
  AND a.feedback_id not in (SELECT feedback_id FROM demissaos)
  AND f.gestor_aprovacao_id is not null
  AND f.status_aprovacao_gestor = 'Aprovado'
  AND fc.deleted_at is null
  AND f.deleted_at is null
  AND a.status = 'Admitido'
GROUP BY a.id, a.feedback_id, a.data_admissao, fc.empresa_id, f.periodo_aquisitivo_id, f.qnt_dias, f.qnt_faltas, f.id,
         pa.label, f.data_saida, f.data_retorno, f.dias_saldo
ORDER BY a.id ASC");


foreach ($admissoes as $adm) {
    $dto = [
        'empresa_id' => $adm->empresa_id,
        'admissao_id' => $adm->admissao_id,
        'periodo_aquisitivo_id' => $adm->periodo_aquisitivo_id,
        'total_avos' => 30,
        'historico' => (object)[],
        'atualizado_via_script' => 1,
        'ultima_atualizacao' => (new DataHora())->dataInsert()];

    if ($adm->periodo_aquisitivo_id > 4) {
        $auth = Auth::loginUsingId($adm->empresa_id);
//        echo "NO IF - ADMISSAO_ID : " . $adm->admissao_id . " | Data admissao -  " . $adm->data_admissao . " | Ferias ID -  " . $adm->ferias_id . " | " . $adm->nome . " | SAIDA - " . $adm->data_saida . " | RETORNO - " . $adm->data_retorno . " | " . $adm->periodo_aquisitivo_label . " | qnt_dias " . $adm->qnt_dias . " | qnt_faltas " . $adm->qnt_faltas . " | saldo " . $adm->dias_saldo . ".\n";
        dd($adm);
        if ($adm->status_ferias == 'gozada' || $adm->status_ferias == 'gozando') {
//            \App\Models\FeriasCalculoAvos::updateOrCreate([
//                'empresa_id' => $adm->empresa_id,
//                'admissao_id' => $adm->admissao_id,
//                'periodo_aquisitivo_id' => $adm->periodo_aquisitivo_id,
//                'atualizado_via_script' => 1,
//            ], $dto);
        } else if (is_null($adm->status_ferias)) {

//            $admissao_id = $adm->admissao_id;
//            $data_admissao = $adm->data_admissao;
//            $dia_admissao = (new DataHora($data_admissao))->dia();
//            $mes_admissao = (new DataHora($data_admissao))->mes();
//            $ano_admissao = (new DataHora($data_admissao))->ano();
//            $periodo_aquisitivo_admissao = \App\Models\PeriodoAquisitivo::where('ano_inicial', $ano_admissao)->first();
//            $empresa_id = $adm->empresa_id;
//            $historico_avos = \App\Models\FeriasCalculoAvos::somaAvosScript($dia_admissao, $mes_admissao, $ano_admissao, $periodo_aquisitivo);
//            $ultimo_total_avos_admissao = $historico_avos[$ano_admissao]['total_avos'];
//            $historico_avos_admissao = $historico_avos[$ano_admissao];
//            unset($historico_avos_admissao['total_avos']);
//            $historico_avos_cad_admissao = json_encode(array_values(json_decode(json_encode($historico_avos_admissao), true)));
//            $calculo_avos_admissao = [
//                'empresa_id' => $empresa_id,
//                'admissao_id' => $admissao_id,
//                'periodo_aquisitivo_id' => $periodo_aquisitivo_admissao,
//                'total_avos' => $ultimo_total_avos_admissao,
//                'historico' => $historico_avos_cad_admissao,
//                'atualizado_via_script' => true,
//                'ultima_atualizacao' => (new DataHora())->dataHoraInsert(),
//            ];
//
//
//            \App\Models\FeriasCalculoAvos::updateOrCreate([
//                'empresa_id' => $empresa_id,
//                'admissao_id' => $admissao_id,
//                'periodo_aquisitivo_id' => $periodo_aquisitivo_admissao,
//                'atualizado_via_script' => true,
//            ], $calculo_avos_admissao);

        }
    } else {
//        echo "SEM IF - ADMISSAO_ID : " . $adm->admissao_id . " | Data admissao -  " . $adm->data_admissao . " | Ferias ID -  " . $adm->ferias_id . " | " . $adm->nome . " | SAIDA - " . $adm->data_saida . " | RETORNO - " . $adm->data_retorno . " | " . $adm->periodo_aquisitivo_label . " | qnt_dias " . $adm->qnt_dias . " | qnt_faltas " . $adm->qnt_faltas . " | saldo " . $adm->dias_saldo . ".\n";
//        $auth = Auth::loginUsingId($adm->empresa_id);
//        $upC = \App\Models\FeriasCalculoAvos::updateOrCreate([
//            'empresa_id' => $adm->empresa_id,
//            'admissao_id' => $adm->admissao_id,
//            'periodo_aquisitivo_id' => $adm->periodo_aquisitivo_id,
//            'atualizado_via_script' => 1,
//        ], $dto);
    }
}


die();

$periodos_aquisitivos = DB::select("SELECT * FROM periodos_aquisitivos WHERE ano_inicial >= 2018");
$periodo_aquisitivo = [];
foreach ($periodos_aquisitivos as $pa) {
    $periodo_aquisitivo[$pa->ano_inicial] = [
        'id' => $pa->id,
        'label' => $pa->label,
        'ano_inicial' => $pa->ano_inicial,
        'ano_final' => $pa->ano_final,
    ];
}

$hoje = (new DataHora())->dataInsert();
$mes_atual = (new DataHora())->mes();
$ano_atual = (new DataHora())->ano();

foreach ($admissoes as $key => $linha) {
    $admissao_id = $linha->admissao_id;
    $data_admissao = $linha->data_admissao;
    $dia_admissao = (new DataHora($data_admissao))->dia();
    $mes_admissao = (new DataHora($data_admissao))->mes();
    $ano_admissao = (new DataHora($data_admissao))->ano();
    $periodo_aquisitivo_admissao = $periodo_aquisitivo[$ano_admissao]['id'];
    $empresa_id = $linha->empresa_id;
    $historico_avos = \App\Models\FeriasCalculoAvos::somaAvosScriptNew($dia_admissao, $mes_admissao, $ano_admissao, $periodo_aquisitivo);

    $ultimo_total_avos_admissao = $historico_avos[$ano_admissao]['total_avos'];
    $historico_avos_admissao = $historico_avos[$ano_admissao];
    unset($historico_avos_admissao['total_avos']);
    $historico_avos_cad_admissao = json_encode(array_values(json_decode(json_encode($historico_avos_admissao), true)));
    $calculo_avos_admissao = [
        'empresa_id' => $empresa_id,
        'admissao_id' => $admissao_id,
        'periodo_aquisitivo_id' => $periodo_aquisitivo_admissao,
        'total_avos' => $ultimo_total_avos_admissao,
        'historico' => $historico_avos_cad_admissao,
        'atualizado_via_script' => true,
        'ultima_atualizacao' => (new DataHora())->dataHoraInsert(),
    ];

    $periodo_aquisitivo_cad_admissao = $periodo_aquisitivo_admissao;

    try {
        $cadastrado = DB::table('ferias_calculo_avos')
            ->where('admissao_id', $admissao_id)
            ->where('periodo_aquisitivo_id', $periodo_aquisitivo_cad_admissao)
            ->count();

        if ($cadastrado == 0 && $ultimo_total_avos_admissao > 0) {
            $cadAsos = DB::table('ferias_calculo_avos')->insert($calculo_avos_admissao);
            echo "ID : " . $linha->admissao_id . " | " . $cont++ . " CADASTRADA\n";
        } else {
            echo "ID : " . $linha->admissao_id . " | " . $cont++ . " JÁ CADASTRADA\n";
        }

    } catch (Exception $exception) {
        echo $exception->getMessage();
    }
}
