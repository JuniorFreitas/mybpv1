<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\Excel\Relatorios\JobExportaFeriasExcel;
use App\Jobs\Excel\Relatorios\JobExportaVencimentoFeriasExcel;
use App\Models\Admissao;
use App\Models\ClienteConfig;
use App\Models\Ferias;
use App\Models\FeriasCalculoAvos;
use App\Models\PeriodoAquisitivo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class FeriasController extends Controller
{
    const UmAno8Meses = 546;
    const UmAno10Meses = 607;

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
        $result = Admissao::select(['ferias.id as ferias_id', 'admissoes.id as admissao_id', 'users.nome', 'curriculos.nome',
            'admissoes.feedback_id', 'admissoes.data_admissao', 'admissoes.cargo', 'admissoes.centro_custo_id', 'centro_custos.label as centro_custo_label', 'admissoes.funcao',
            'feedback_curriculos.empresa_id', 'ferias.periodo_aquisitivo_id',
            'ferias.qnt_dias', 'ferias.qnt_faltas', 'ferias.dias_saldo', 'ferias.data_saida', 'ferias.data_retorno',
            'ferias.status_ferias', 'ferias.aprovado_via_script', 'ferias.status_aprovacao_gestor', 'ferias.gestor_aprovacao_id',
            'ferias.deleted_at', 'users.nome as gestor_aprovacao_nome', 'rh.nome as rh_aprovacao_nome', 'periodos_aquisitivos.label as periodo_aquisitivo_label', 'periodos_aquisitivos.ano_inicial as periodo_aquisitivo_ano_inicial',
        ])
            ->join('feedback_curriculos', 'admissoes.feedback_id', '=', 'feedback_curriculos.id')
            ->leftJoin('ferias', 'admissoes.id', '=', 'ferias.admissao_id')
            ->leftJoin('curriculos', 'feedback_curriculos.curriculo_id', '=', 'curriculos.id')
            ->leftJoin('users', 'feedback_curriculos.empresa_id', '=', 'users.id')
            ->leftJoin('centro_custos', 'admissoes.centro_custo_id', '=', 'centro_custos.id')
            ->leftJoin('centro_custo_filials', 'admissoes.centro_custo_filial_id', '=', 'centro_custo_filials.id')
            ->join('periodos_aquisitivos', 'ferias.periodo_aquisitivo_id', '=', 'periodos_aquisitivos.id')
            ->leftJoin('users as gestor', 'ferias.gestor_aprovacao_id', '=', 'gestor.id')
            ->leftJoin('users as rh', 'ferias.rh_aprovacao_id', '=', 'rh.id')
            ->where('feedback_curriculos.empresa_id', auth()->user()->empresa_id)
            ->whereNotIn('admissoes.feedback_id', function ($query) {
                $query->select('feedback_id')->from('demissaos');
            })
            ->whereNotNull('ferias.gestor_aprovacao_id')
            ->where('ferias.status_aprovacao_gestor', 'Aprovado')
            ->whereNull('feedback_curriculos.deleted_at')
            ->whereNull('ferias.deleted_at')
            ->where('admissoes.status', 'Admitido')
            ->groupBy('admissoes.id', 'admissoes.feedback_id', 'admissoes.data_admissao', 'feedback_curriculos.empresa_id',
                'ferias.periodo_aquisitivo_id', 'ferias.qnt_dias', 'ferias.qnt_faltas', 'ferias.id', 'periodos_aquisitivos.label', 'periodos_aquisitivos.ano_inicial',
                'ferias.data_saida', 'ferias.data_retorno', 'ferias.dias_saldo')
            ->orderBy('admissoes.id', 'asc')
            ->get()->groupBy('admissao_id')->values()
            ->map(function ($item) {
                $dias_atraso = 0;
                $todos_periodos = FeriasCalculoAvos::select(['ferias_calculo_avos.id', 'ferias_calculo_avos.admissao_id', 'ferias_calculo_avos.periodo_aquisitivo_id',
                    'ferias_calculo_avos.empresa_id', 'ferias_calculo_avos.total_avos', 'ferias_calculo_avos.ultima_atualizacao', 'ferias_calculo_avos.atualizado_via_script',
                    'periodos_aquisitivos.label', 'periodos_aquisitivos.ano_inicial'])
                    ->join('periodos_aquisitivos', 'ferias_calculo_avos.periodo_aquisitivo_id', '=', 'periodos_aquisitivos.id')
                    ->whereAdmissaoId($item->first()->admissao_id)->orderByDesc('periodos_aquisitivos.ano_inicial')->get()->map(function ($t) use ($item) {
                        $ferias = $item->where('admissao_id', $t->admissao_id)->where('periodo_aquisitivo_id', $t->periodo_aquisitivo_id)->first();

                        $status_ferias = 'Aguardando';
                        if ($t->total_avos < 30) {
                            $status_ferias = 'Saldo insuficiente';
                        } elseif (is_null($ferias) && (int)$t->ano_inicial <= 2020) {
                            $status_ferias =  'Gozada';
                        }else{
                            $status_ferias = $ferias->status_ferias ?? 'Aguardando';
                        }
                        return [
                            'id' => $t->id,
                            'admissao_id' => $t->admissao_id,
                            'empresa_id' => $t->empresa_id,
                            'total_avos' => $t->total_avos,
                            'periodo_aquisitivo' => $t->label,
                            'data_saida' => $ferias->data_saida ?? null,
                            'data_retorno' => $ferias->data_retorno ?? null,
                            'ultima_atualizacao' => (new DataHora($t->ultima_atualizacao))->dataCompleta(),
                            'atualizado_via_script' => $t->atualizado_via_script,
                            'status_ferias' => $status_ferias,
                            'tem_tb_ferias' => (bool)$ferias
                        ];
                    });
                $atraso = $item->map(function ($item) use (&$dias_atraso) {
                    if (is_null($item->status_ferias)) {
                        $dias_atraso = Carbon::now()->diffInDays($item->data_saida);
                    }
                    return $dias_atraso;
                });


                switch ($atraso->sum) {
                    case $atraso->sum() <= self::UmAno8Meses:
                        $colorir = 'bg-white';
                        break;
                    case $atraso->sum() >= self::UmAno8Meses + 1 && $atraso->sum() <= self::UmAno10Meses:
                        $colorir = 'bg-warning';
                        break;
                    default:
                        $colorir = 'bg-danger text-white';
                        break;
                }

                return [
                    'dias_atraso' => $atraso->sum(),
                    'tempo_atrasado' => Carbon::now()->subDays($atraso->sum())->diffForHumans(),
                    'pintar' => $colorir,
                    'nome' => $item->first()->nome,
                    'cargo' => $item->first()->cargo,
                    'funcao' => $item->first()->funcao,
                    'data_admissao' => $item->first()->data_admissao,
                    'centro_custo' => !is_null($item->first()->centro_custo_id) ? $item->first()->centro_custo_label : 'Não informado',
                    'todos_periodos' => $todos_periodos,
                    'periodos' => $item->map(function ($item) {
                        return [
                            'id' => $item->ferias_id,
                            'periodo_aquisitivo' => $item->periodo_aquisitivo_label,
                            'periodo_aquisitivo_ano_inicial' => $item->periodo_aquisitivo_ano_inicial,
                            'qnt_dias' => $item->qnt_dias,
                            'qnt_faltas' => $item->qnt_faltas,
                            'dias_saldo' => $item->dias_saldo,
                            'data_saida' => $item->data_saida,
                            'data_retorno' => $item->data_retorno,
                            'status_ferias' => $item->status_ferias,
                            'aprovado_via_script' => $item->aprovado_via_script,
                            'status_aprovacao_gestor' => $item->status_aprovacao_gestor,
                            'gestor_aprovacao_id' => $item->gestor_aprovacao_id,
                            'gestor_aprovacao_nome' => $item->gestor_aprovacao_nome,
                            'deleted_at' => $item->deleted_at,
                            'avos' => FeriasCalculoAvos::select(['total_avos', 'ultima_atualizacao'])->where('admissao_id', $item->admissao_id)
                                ->where('periodo_aquisitivo_id', $item->periodo_aquisitivo_id)
                                ->first()
                        ];
                    })->sortByDesc('periodo_aquisitivo_ano_inicial')->values(),
                ];
            });

        return $result->sortByDesc('dias_atraso')->values();

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
        } else {
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
