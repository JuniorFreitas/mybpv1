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

$muda_cargo_prevista = DB::select("SELECT
        mcp.id as muda_cargo_id,
        mcp.cliente_id,
        mcp.colaborador_id,
        mcp.centro_custo_id as novo_centro_custo_id,
        mcp.cargo_anterior_id as anterior_vaga_aberta_id,
        mcp.salario_anterior as anterior_salario,
        mcp.novo_cargo_id as nova_vaga_aberta_id,
        mcp.novo_salario,
        mcp.gestor_id as gestor_id,
        mcp.user_id as solicitante_id,
        mcp.obs as obs_solicitante,
        mcp.user_aprovacao_id as gestor_aprovacao_id,
        mcp.data_aprovacao as data_aprovacao_gestor,
        mcp.obs_aprovacao as obs_gestor_aprovacao,
        mcp.status_aprovacao as status_aprovacao_gestor,
        mcp.empresa_id,
        mcp.created_at as data_solicitacao,
        mcpa.arquivo_id,
        a.id as admissao_id,
        a.centro_custo_filial_id as anterior_centro_custo_filial_id,
        a.filial as anterior_filial,
        a.centro_custo_id as anterior_centro_custo_id,
        a.funcao as anterior_funcao,
        u.id as user_id
    FROM
        muda_cargo_previstas mcp
        INNER JOIN muda_cargo_previstas_anexos mcpa on mcp.id = mcpa.muda_cargo_prevista_id
        INNER JOIN users u on mcp.colaborador_id = u.id
        INNER JOIN feedback_curriculos fc on u.id = fc.curriculo_id
        INNER JOIN admissoes a on a.feedback_id = fc.id
    WHERE
        mcp.status_aprovacao = 'aprovado'
    ORDER BY mcp.data_aprovacao asc");


$hoje = (new DataHora())->dataHoraInsert();
$cont = 0;
foreach ($muda_cargo_prevista as $key => $linha) {
    $mcp[$key] = [
        'empresa_id' => $linha->empresa_id,
        'admissao_id' => $linha->admissao_id,
        'colaborador_id' => $linha->colaborador_id,
        'mantem_centro_custo' => is_null($linha->novo_centro_custo_id),
        'anterior_centro_custo_id' => $linha->anterior_centro_custo_id,
        'novo_centro_custo_id' => $linha->novo_centro_custo_id,
        'mantem_cargo' => is_null($linha->nova_vaga_aberta_id),
        'anterior_vaga_aberta_id' => $linha->anterior_vaga_aberta_id,
        'nova_vaga_aberta_id' => $linha->nova_vaga_aberta_id,
        'mantem_funcao' => true,
        'anterior_funcao' => $linha->anterior_funcao,
        'mantem_salario' => is_null($linha->novo_salario),
        'anterior_salario' => $linha->anterior_salario,
        'novo_salario' => $linha->novo_salario,
        'solicitante_id' => $linha->solicitante_id,
        'obs_solicitante' => $linha->obs_solicitante,
        'data_solicitacao' => $linha->data_solicitacao,
        'gestor_id' => $linha->gestor_id,
        'gestor_aprovacao_id' => $linha->gestor_aprovacao_id,
        'obs_gestor_aprovacao' => $linha->obs_gestor_aprovacao,
        'status_aprovacao_gestor' => $linha->status_aprovacao_gestor,
        'data_aprovacao_gestor' => $linha->data_aprovacao_gestor,
        'rh_aprovacao_id' => $linha->empresa_id,
        'status_aprovacao_rh' => $linha->status_aprovacao_gestor,
        'data_aprovacao_rh' => $linha->data_aprovacao_gestor,
        'aprovado_via_script' => true,
        'created_at' => $hoje
    ];

    try {
        $mudanca_cargo_insert_id = DB::table('mudanca_cargo')->insertGetId($mcp[$key]);
        if(!is_null($linha->arquivo_id)){
            $mcpa_dados = [
              'mudanca_cargo_id' => $mudanca_cargo_insert_id,
              'arquivo_id' => $linha->arquivo_id
            ];
            DB::table('mudanca_cargo_anexos')->insert($mcpa_dados);
        }
        echo "ID : " . $linha->muda_cargo_id . " | " . $cont++ . " CADASTRADA\n";
    } catch (Exception $exception) {
        echo $exception->getMessage();
    }
}
