<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\JobExportaExcel;
use App\Models\ClienteConfig;
use App\Models\Ferias;
use App\Models\FeriasAdquiridas;
use App\Models\PeriodoAquisitivo;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class FeriasController extends Controller
{
    public function index()
    {
        return view('g.relatorios.ferias.index');
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

    public function show(Request $request)
    {
        if ($request->filled('periodo_range') && $request->filled('tipo') && $request->tipo == 'data') {
            $periodo = explode(' até ', $request->periodo_range);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
        }

        $periodo_vencimento = ClienteConfig::LISTA_VENCIMENTOS[auth()->user()->EmpresaConfiguracoes->verifica_mes_vencimento];
        $periodo_vencimento = preg_replace("/[^0-9]/", "", $periodo_vencimento);

        $queryResult = Ferias::with('FeriasPrevista',
            'PeriodoAquisitivo',
            'Gestor',
            'GestorAprovacao',
            'Solicitante',
            'Admissao',
            'Admissao.CentroCusto',
            'Admissao.Feedback',
            'Admissao.Feedback.VagaSelecionada',
            'Admissao.Feedback.Curriculo',
            'Admissao.Feedback.Curriculo',
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
                'nome' => $ferias['admissao']['feedback']['curriculo']['nome'],
                'cargo' => $ferias['admissao']['cargo'],
                'funcao' => $ferias['admissao']['funcao'],
                'data_admissao' => $ferias['admissao']['data_admissao'],
                'gestor' => $ferias['gestor_aprovacao']['nome'] ?? '',
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
                'status' => $ferias['status_ferias'],
                'status_aprovacao' => $ferias['status_aprovacao_gestor'],
//                'resposta_rh' => $ferias['ferias_prevista']['resposta_rh'],
//                'data_aprovacao_rh' => $ferias['ferias_prevista']['data_aprovacao_rh'],
//                'data_aprovacao' => $ferias['ferias_prevista']['data_aprovacao'],
//                'rh' => $ferias['ferias_prevista']['rh_aprovacao']['nome'] ?? '',
                'dias_vencer' => $dias_vencer,
                'pintar' => $dias_vencer <= $periodo_vencimento / 2,
            ]);
        }


        return [
            'dados' => $resultado->sortBy('dias_vencer')->values()->all(),
        ];

        $feriasAdquiridas = FeriasAdquiridas::
        where('data_saida', '>=', $dataInicio->dataInsert())
            ->where('data_saida', '<=', $dataFim->dataInsert())
            ->whereNotIn('status', [FeriasAdquiridas::STATUS_GOZANDO, FeriasAdquiridas::STATUS_GOZADA])
            ->whereHas('Admissao.Feedback', function ($q) {
                $q->Admitidos();
            })->with(['Admissao.Feedback' => function ($F) {
                $F->Admitidos()->select(['id', 'curriculo_id', 'empresa_id', 'vaga_id'])
                    ->with('VagaSelecionada:id,nome')
                    ->with('Curriculo:id,nome,nascimento,rg,orgao_expeditor');
            }, 'UsuarioCadastrou:id,nome', 'FeriasPrevista.CentroCusto:id,label', 'FeriasPrevista.GestorAprovacao:id,nome', 'FeriasPrevista.RhAprovacao:id,nome', 'FeriasPrevista.PeriodoAquisitivo', 'FeriasPrevista.QuemAprovou:id,nome']);

        $feriasAdquiridas = $feriasAdquiridas->get()->toArray();

        $resultado = collect();

        foreach ($feriasAdquiridas as $ferias) {
            $dias_vencer = DataHora::diferencaDias((new DataHora())->dataInsert(), $ferias['data_limite']);
            $resultado->push([
                'nome' => $ferias['admissao']['feedback']['curriculo']['nome'],
                'cargo' => $ferias['admissao']['feedback']['vaga_selecionada']['nome'],
                'data_admissao' => $ferias['admissao']['data_admissao'],
                'gestor' => $ferias['ferias_prevista']['gestor_aprovacao']['nome'] ?? '',
                'quem_aprovou' => $ferias['ferias_prevista']['quem_aprovou']['nome'] ?? '',
                'centro_custo' => $ferias['ferias_prevista']['centro_custo']['label'] ?? '',
                'periodo_gozado' => $ferias['periodo_gozado'],
                'qnt_dias' => $ferias['qnt_dias'],
                'dias_saldo' => $ferias['ferias_prevista']['dias_saldo'],
                'tem_faltas' => $ferias['ferias_prevista']['tem_faltas'] ? 'Sim' : 'Não',
                'qnt_faltas' => $ferias['ferias_prevista']['qnt_faltas'],
                'periodo_aquisitivo' => $ferias['ferias_prevista']['periodo_aquisitivo']['label'],
                'data_saida' => $ferias['data_saida'],
                'data_retorno' => $ferias['data_retorno'],
                'proximo_periodo' => $ferias['proximo_periodo'],
                'data_limite' => $ferias['data_limite'],
                'ultima_data' => $ferias['ferias_prevista']['ultima_data'],
                'usuario_cadastrou' => $ferias['usuario_cadastrou']['nome'],
                'status' => $ferias['status'],
                'status_aprovacao' => $ferias['ferias_prevista']['status_aprovacao'],
                'resposta_rh' => $ferias['ferias_prevista']['resposta_rh'],
                'data_aprovacao_rh' => $ferias['ferias_prevista']['data_aprovacao_rh'],
                'data_aprovacao' => $ferias['ferias_prevista']['data_aprovacao'],
                'rh' => $ferias['ferias_prevista']['rh_aprovacao']['nome'] ?? '',
                'dias_vencer' => $dias_vencer,
                'pintar' => $dias_vencer <= $periodo_vencimento / 2,
            ]);
        }

        return $resultado->sortBy('dias_vencer')->values()->all();

    }

    public function exportExcel(Request $request)
    {
        $ferias = $this->show($request);

        $head = [
            'nome',
            'cargo',
            'data_admissao',
            'gestor',
            'quem_aprovou',
            'centro_custo',
            'data_saida',
            'data_retorno',
            'qnt_dias',
            'dias_saldo',
            'tem_faltas',
            'qnt_faltas',
            'periodo_aquisitivo',
            'ultima_data',
            'status_aprovacao',
            'data_aprovacao',
            'rh',
            'resposta_rh',
            'data_aprovacao_rh',
            'dias_vencer',
        ];
        $rows = [];

        foreach ($ferias as $row) {
            $rows[] = array(
                $row['nome'],
                $row['cargo'],
                $row['data_admissao'],
                $row['gestor'],
                $row['quem_aprovou'],
                $row['centro_custo'],
                $row['data_saida'],
                $row['data_retorno'],
                $row['qnt_dias'],
                $row['dias_saldo'],
                $row['tem_faltas'],
                $row['qnt_faltas'],
                $row['periodo_aquisitivo'],
                $row['ultima_data'],
                $row['status_aprovacao'],
                $row['data_aprovacao'],
                $row['rh'],
                $row['resposta_rh'],
                $row['data_aprovacao_rh'],
                $row['dias_vencer'],
            );
        }

        $nameArquivo = "vencimento_ferias_" . \Str::slug('Vencimento Ferias') . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Vencimento Ferias ", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }
}
