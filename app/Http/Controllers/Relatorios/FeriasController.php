<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\Excel\Relatorios\JobExportaFeriasExcel;
use App\Jobs\Excel\Relatorios\JobExportaVencimentoFeriasExcel;
use App\Models\ClienteConfig;
use App\Models\Ferias;
use App\Models\FeriasCalculoAvos;
use App\Models\PeriodoAquisitivo;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class FeriasController extends Controller
{
    public function index()
    {
        return view('g.relatorios.ferias.index');
    }

    public function indexVencimentoFerias()
    {
        return view('g.relatorios.vencimentoferias.index');
    }

    public function listaperiodos()
    {
        $periodosAquisitivos = PeriodoAquisitivo::orderByDesc('ano_final')->limit(3)->get()->toArray();
        return [
            'filtro' => [
                'periodo_aquisitivo' => $periodosAquisitivos,
                'status_ferias' => Ferias::LISTA_RELATORIO_VENCIMENTO_FERIAS,
            ]
        ];
    }

    public function showVencimentoFerias(Request $request)
    {
        $queryResult = FeriasCalculoAvos::with(
            'PeriodoAquisitivo',
            'Admissao:id,centro_custo_id,cargo,funcao,data_admissao,feedback_id',
            'Admissao.CentroCusto',
            'Admissao.Feedback:id,curriculo_id,vagas_abertas_id',
            'Admissao.Feedback.VagaSelecionada',
            'Admissao.Feedback.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'Admissao.CentroCusto:id,label'
        )->whereHas('Admissao', function ($query) {
            $query->admitidos();
        })->where('total_avos', '>=', 25 );

        if ($request->filled('periodo')) {
            $queryResult->where('periodo_aquisitivo_id', $request->periodo);
        }

        $queryResult = $queryResult->orderBy('total_avos', 'desc')->get()->toArray();

        $resultado = collect();

        foreach ($queryResult as $avos) {
            $resultado->push([
                'atualizado_via_script' => $avos['atualizado_via_script'] ? 'Sim' : 'Não',
                'avos_id' => $avos['id'],
                'nome' => $avos['admissao']['feedback']['curriculo']['nome'],
                'cargo' => $avos['admissao']['cargo'],
                'funcao' => $avos['admissao']['funcao'],
                'data_admissao' => $avos['admissao']['data_admissao'],
                'ultima_atualizacao' => $avos['ultima_atualizacao'],
                'centro_custo' => !is_null($avos['admissao']['centro_custo_id']) ? $avos['admissao']['centro_custo']['label'] : 'Não informado',
                'periodo_aquisitivo' => $avos['periodo_aquisitivo']['label'],
                'total_avos' => $avos['total_avos']
            ]);
        }

        return [
            'dados' => $resultado->values()->all(),
        ];

    }

    public function show(Request $request)
    {
        if ($request->filled('periodo_range') && $request->filled('tipo') && $request->tipo == 'data') {
            $periodo = explode(' até ', $request->periodo_range);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
        }

        $periodo_vencimento = ClienteConfig::LISTA_VENCIMENTOS[auth()->user()->EmpresaConfiguracoes->verifica_mes_vencimento];
        $periodo_vencimento = preg_replace("/[^0-9]/", "", $periodo_vencimento);

        $queryResult = Ferias::with(
            'PeriodoAquisitivo',
            'Gestor:id,nome',
            'GestorAprovacao:id,nome',
            'RhAprovacao:id,nome',
            'Solicitante:id,nome',
            'Admissao:id,centro_custo_id,cargo,funcao,data_admissao,feedback_id',
            'Admissao.CentroCusto',
            'Admissao.Feedback:id,curriculo_id,vagas_abertas_id',
            'Admissao.Feedback.VagaSelecionada',
            'Admissao.Feedback.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'Admissao.CentroCusto:id,label',
            'FeriasPrevista:id,centro_custo_id',
            'FeriasPrevista.CentroCusto:id,label',
        )->whereHas('Admissao', function ($query) {
            $query->admitidos();
        });

        if ($request->filled('tipo')) {
            if ($request->tipo == 'aquisitivo') {
                $queryResult->where('periodo_aquisitivo_id', $request->periodo);
            } else {
                $queryResult->where('data_saida', '>=', $dataInicio->dataInsert())
                    ->where('data_saida', '<=', $dataFim->dataInsert());
            }
        }
        if ($request->filled('status_ferias')) {
            $queryResult->where('status_ferias', $request->status_ferias);
        }else{
            $queryResult->whereIn('status_ferias', Ferias::LISTA_RELATORIO_VENCIMENTO_FERIAS);
        }

        $queryResult = $queryResult->get()->toArray();

        $resultado = collect();

        foreach ($queryResult as $ferias) {
            $dias_vencer = DataHora::diferencaDias((new DataHora())->dataInsert(), $ferias['data_saida']);
            $resultado->push([
                'dias_vencer' => $dias_vencer,
                'pintar' => $dias_vencer <= $periodo_vencimento / 2,
                'aprovado_via_script' => $ferias['aprovado_via_script'] ? 'Sim' : 'Não',
                'ferias_id' => $ferias['id'],
                'status' => $ferias['status_ferias'],
                'nome' => $ferias['admissao']['feedback']['curriculo']['nome'],
                'cargo' => $ferias['admissao']['cargo'],
                'funcao' => $ferias['admissao']['funcao'],
                'data_admissao' => $ferias['admissao']['data_admissao'],
                'gestor' => $ferias['gestor_aprovacao']['nome'] ?? '---',
                'quem_aprovou' => $ferias['gestor']['nome'] ?? '---',
                'status_aprovacao' => $ferias['status_aprovacao_gestor'],
                'data_aprovacao' => $ferias['data_aprovacao_gestor'],
                'centro_custo' => !is_null($ferias['admissao']['centro_custo_id']) ? $ferias['admissao']['centro_custo']['label'] : $ferias['ferias_prevista']['centro_custo']['label'],
                'qnt_dias' => $ferias['qnt_dias'],
                'dias_saldo' => $ferias['dias_saldo'],
                'tem_faltas' => $ferias['tem_faltas'] ? 'Sim' : 'Não',
                'qnt_faltas' => $ferias['qnt_faltas'],
                'periodo_aquisitivo' => $ferias['periodo_aquisitivo']['label'],
                'data_saida' => $ferias['data_saida'],
                'data_retorno' => $ferias['data_retorno'],
                'ultima_data' => $ferias['ultima_data'],
                'usuario_cadastrou' => $ferias['solicitante']['nome'],
                'resposta_rh' => strlen(trim($ferias['status_aprovacao_rh'])) > 0 ? $ferias['status_aprovacao_rh'] : '---',
                'data_aprovacao_rh' => $ferias['data_aprovacao_rh'],
                'rh' => $ferias['aprovado_via_script'] ? 'POR AUTOMAÇÃO' : $ferias['rh_aprovacao']['nome'] ?? '',
            ]);
        }

        return [
            'dados' => $resultado->sortBy('dias_vencer')->values()->all(),
        ];

    }

    public function exportExcel(Request $request)
    {
        $ferias = $this->show($request)['dados'];
        $nameArquivo = "ferias_" . \Str::slug('Ferias') . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaFeriasExcel::dispatch(auth()->id(), "Ferias ", $ferias, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

    public function exportExcelVencimentoFerias(Request $request)
    {
        $vencimentoFerias = $this->showVencimentoFerias($request)['dados'];
        $nameArquivo = "vencimento_ferias_" . \Str::slug('Vencimento Ferias') . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaVencimentoFeriasExcel::dispatch(auth()->id(), "Vencimento Ferias ", $vencimentoFerias, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }
}
