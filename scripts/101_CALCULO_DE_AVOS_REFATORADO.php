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
            a.id as admissao_id, a.feedback_id, a.data_admissao, fc.empresa_id
        FROM admissoes a
            INNER JOIN feedback_curriculos fc on a.feedback_id = fc.id
        WHERE a.data_admissao >= '1996-01-01'
        AND a.feedback_id not in (SELECT
                feedback_id
            FROM demissaos)
        AND fc.deleted_at is null
        ORDER BY a.data_admissao ASC");

$periodos_aquisitivos = DB::select("SELECT * FROM periodos_aquisitivos WHERE ano_inicial >= 1996 ORDER BY ano_inicial ASC");
$periodo_aquisitivo = [];
foreach ($periodos_aquisitivos as $pa){
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

    foreach ($historico_avos as $chave => $historico){
        $ultimo_total_avos_admissao = $historico_avos[$chave]['total_avos'];
        $historico_avos_admissao = $historico_avos[$chave];
        unset($historico_avos_admissao['total_avos']);
        $historico_avos_cad_admissao = json_encode(array_values(json_decode(json_encode($historico_avos_admissao), true)));
        $calculo_avos_admissao = [
            'empresa_id' => $empresa_id,
            'admissao_id' => $admissao_id,
            'periodo_aquisitivo_id' => $periodo_aquisitivo[$chave]['id'],
            'total_avos' => $ultimo_total_avos_admissao,
            'historico' => $historico_avos_cad_admissao,
            'atualizado_via_script' => true,
            'ultima_atualizacao' => (new DataHora())->dataHoraInsert(),
        ];

        $periodo_aquisitivo_cad_admissao = $periodo_aquisitivo_admissao;

        try {
            $periodo_aquisitivo_id = (int) $periodo_aquisitivo[$chave]['id'];
            $cadastrado = DB::table('ferias_calculo_avos')
                ->where('admissao_id', $admissao_id)
                ->where('periodo_aquisitivo_id', $periodo_aquisitivo_id)
                ->get();

            if (count($cadastrado) == 0 && $ultimo_total_avos_admissao > 0) {
                $cadAsos = DB::table('ferias_calculo_avos')->insert($calculo_avos_admissao);
                echo "ID : " . $linha->admissao_id . " | " . $cont++ . " CADASTRADA\n";
            } else {
                echo "ID : " . $linha->admissao_id . " | " . $cont++ . " JÁ CADASTRADA\n";
            }

        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }
}
