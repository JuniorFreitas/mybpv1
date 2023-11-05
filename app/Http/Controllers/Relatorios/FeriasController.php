<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\Excel\Relatorios\JobExportaFeriasExcel;
use App\Jobs\Excel\Relatorios\JobExportaVencimentoFeriasExcel;
use App\Jobs\Relatorios\Ferias\Vencimento\JobExportarExcel;
use App\Models\Admissao;
use App\Models\ClienteConfig;
use App\Models\Ferias;
use App\Models\FeriasCalculoAvos;
use App\Models\PeriodoAquisitivo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use MasterTag\DataHora;

class FeriasController extends Controller
{
    const DEZOITOMESES = 546;
    const VINTEMESES = 607;

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
        $empresa_id = auth()->user()->empresa_id;

        if (\Cache::has($this->nomeRelatorio())) {
            $result = json_decode(\Cache::get($this->nomeRelatorio()), true);
        } else {
            $result = Admissao::select(['ferias.id as ferias_id', 'admissoes.id as admissao_id', 'users.nome', 'curriculos.nome',
                'admissoes.feedback_id', 'admissoes.data_admissao', 'admissoes.cargo', 'admissoes.centro_custo_id', 'centro_custos.label as centro_custo_label', 'admissoes.funcao',
                'feedback_curriculos.empresa_id', 'ferias.periodo_aquisitivo_id',
                'ferias.qnt_dias', 'ferias.qnt_faltas', 'ferias.dias_saldo', 'ferias.data_saida', 'ferias.data_retorno',
                'ferias.status_ferias', 'ferias.aprovado_via_script', 'ferias.status_aprovacao_gestor', 'ferias.gestor_aprovacao_id',
                'ferias.deleted_at', 'users.nome as gestor_aprovacao_nome', 'rh.nome as rh_aprovacao_nome', 'periodos_aquisitivos.label as periodo_aquisitivo_label', 'periodos_aquisitivos.ano_inicial as periodo_aquisitivo_ano_inicial',
            ])->selectRaw('DATEDIFF(NOW(), ferias.data_retorno) as atraso')
                ->whereDoesntHave('Afastamento')
                ->join('feedback_curriculos', 'admissoes.feedback_id', '=', 'feedback_curriculos.id')
                ->leftJoin('ferias', 'admissoes.id', '=', 'ferias.admissao_id')
                ->leftJoin('curriculos', 'feedback_curriculos.curriculo_id', '=', 'curriculos.id')
                ->leftJoin('users', 'feedback_curriculos.empresa_id', '=', 'users.id')
                ->leftJoin('centro_custos', 'admissoes.centro_custo_id', '=', 'centro_custos.id')
                ->leftJoin('centro_custo_filials', 'admissoes.centro_custo_filial_id', '=', 'centro_custo_filials.id')
                ->join('periodos_aquisitivos', 'ferias.periodo_aquisitivo_id', '=', 'periodos_aquisitivos.id')
                ->leftJoin('users as gestor', 'ferias.gestor_aprovacao_id', '=', 'gestor.id')
                ->leftJoin('users as rh', 'ferias.rh_aprovacao_id', '=', 'rh.id')
                ->where('feedback_curriculos.empresa_id', $empresa_id)
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
                ->get()
                ->groupBy('admissao_id')->values()
                ->map(function ($item) {
                    $todos_periodos = FeriasCalculoAvos::select(['ferias_calculo_avos.id', 'ferias_calculo_avos.admissao_id', 'ferias_calculo_avos.periodo_aquisitivo_id',
                        'ferias_calculo_avos.empresa_id', 'ferias_calculo_avos.total_avos', 'ferias_calculo_avos.ultima_atualizacao', 'ferias_calculo_avos.atualizado_via_script',
                        'ferias_calculo_avos.historico',
                        'periodos_aquisitivos.label', 'periodos_aquisitivos.ano_inicial'])
                        ->join('periodos_aquisitivos', 'ferias_calculo_avos.periodo_aquisitivo_id', '=', 'periodos_aquisitivos.id')
                        ->whereAdmissaoId($item->first()->admissao_id)->orderByDesc('periodos_aquisitivos.ano_inicial')->limit(4)->get()->map(function ($t) use ($item) {

                            $ferias = $item->where('admissao_id', $t->admissao_id)->where('periodo_aquisitivo_id', $t->periodo_aquisitivo_id)->first();
                            if ($t->total_avos < 30) {
                                $status_ferias = 'Saldo insuficiente';
                            } elseif (is_null($ferias) && (int)$t->ano_inicial <= 2020) {
                                $status_ferias = 'Gozada';
                            } else {
                                $status_ferias = isset($ferias->status_ferias) ? ucfirst($ferias->status_ferias == 'aguardando' ? 'Solicitada' : $ferias->status_ferias) : 'Disponivel';
                            }

                            $ultimo_dia_avo = collect($t->historico)->sortByDesc('data_mes')->first()['data_mes'];


                            switch ($status_ferias) {
                                case 'Reprovado':
                                case 'Cancelado':
                                    $colorir = 'badge-danger';
                                    break;
                                case 'Gozada':
                                    $colorir = 'badge-success';
                                    break;
                                case 'Solicitada':
                                    $colorir = 'badge-warning text-black';
                                    break;
                                case 'Aprovado':
                                    $colorir = 'badge-info';
                                    break;
                                case 'Saldo insuficiente':
                                    $colorir = 'badge-soft-pink';
                                    break;
                                default:
                                    $colorir = 'badge-white';
                                    break;
                            }


                            $atraso = Carbon::now()->diffInDays((new DataHora($ultimo_dia_avo))->dataInsert());

                            if ($atraso >= self::VINTEMESES && in_array($status_ferias, ['Disponivel'])) {
                                $colorir = 'badge-danger';
                            }
                            if ($atraso >= self::DEZOITOMESES && $atraso < self::VINTEMESES && in_array($status_ferias, ['Disponivel'])) {
                                $colorir = 'badge-warning';
                            }

                            if ($atraso < self::DEZOITOMESES && $atraso < self::VINTEMESES && in_array($status_ferias, ['Disponivel'])) {
                                $colorir = 'badge-soft-dark';
                            }

                            return [
                                'id' => $t->id,
                                'admissao_id' => $t->admissao_id,
                                'tempo_atrasado' => in_array($status_ferias, ['Disponivel']) && $atraso > 0 ? Carbon::now()->subDays($atraso)->diffForHumans(null, true, false, 2) : null,
                                'colorir' => $colorir,
                                'empresa_id' => $t->empresa_id,
                                'total_avos' => $t->total_avos,
                                'periodo_aquisitivo' => $t->label,
                                'periodo_aquisitivo_id' => $t->periodo_aquisitivo_id,
                                'ultimo_dia_avo' => $ultimo_dia_avo,
                                'data_saida' => isset($ferias->data_saida) ? (new DataHora($ferias->data_saida))->dataCompleta() : null,
                                'data_retorno' => isset($ferias->data_retorno) ? (new DataHora($ferias->data_retorno))->dataCompleta() : null,
                                'ultima_atualizacao' => (new DataHora(collect($t->historico)->sortByDesc('data_mes')->first()['data_mes']))->dataCompleta(),
                                'atualizado_via_script' => $t->atualizado_via_script,
                                'status_ferias' => $status_ferias,
                                'tem_tb_ferias' => (bool)$ferias
                            ];
                        });

                    $atraso = collect($todos_periodos)->where('status_ferias', 'Disponivel')->sortBy('ultimo_dia_avo')->first();

                    if ($atraso) {
                        $atraso = Carbon::now()->diffInDays((new DataHora($atraso['ultimo_dia_avo']))->dataInsert());
                    } else {
                        $atraso = 0;
                    }

                    // Se for maior que 18meses de atraso
                    if ($atraso >= self::VINTEMESES) {
                        $colorir = 'bg-danger text-white';
                    }

                    if ($atraso >= self::DEZOITOMESES && $atraso < self::VINTEMESES) {
                        $colorir = 'bg-warning text-black';
                    }

                    if ($atraso < self::DEZOITOMESES) {
                        $colorir = 'bg-white';
                    }

                    return [
                        'nome' => $item->first()->nome,
                        'cargo' => $item->first()->cargo,
                        'dias_atraso' => $atraso,
                        'tempo_atrasado' => Carbon::now()->subDays($atraso)->diffForHumans(null, true, false, 2),
                        'pintar' => $colorir,
                        'funcao' => $item->first()->funcao,
                        'data_admissao' => $item->first()->data_admissao,
                        'centro_custo' => !is_null($item->first()->centro_custo_id) ? $item->first()->centro_custo_label : 'Não informado',
                        'todos_periodos' => $todos_periodos,
                    ];
                })
                ->sortByDesc('dias_atraso')->values();//Aqui é o filtro dos 360 dias

            ;
            \Cache::set($this->nomeRelatorio(), json_encode($result), 60 * 24);
            Redis::set($this->nomeRelatorio(), \Cache::get($this->nomeRelatorio()));
        }

        if ($request->filled('campoPeriodoVencido')) {
            $result = collect($result)->sortByDesc('dias_atraso')->values()->filter(function ($item) use ($request) {
                switch ($request->campoPeriodoVencido) {
                    case 'apartirdoperiodoconcessivel':
                        $campoPeriodoVencido = $item['dias_atraso'] >= 1 && $item['dias_atraso'] <= 546;
                        break;
                    case '1anoseismesesate1anoe8meses':
                        $campoPeriodoVencido = $item['dias_atraso'] >= 547 && $item['dias_atraso'] <= 607;
                        break;
                    case '1anoe8meseisesuperior':
                        $campoPeriodoVencido = $item['dias_atraso'] >= 608;
                        break;
                    default:
                        $campoPeriodoVencido = $item['dias_atraso'] >= 360;
                        break;
                }
//                return $item['dias_atraso'] >= $campoPeriodoVencido;
                return $item['dias_atraso'] = $campoPeriodoVencido;
            })->values();
        }

        $cargos = collect($result)->unique('cargo')->pluck('cargo')->sort()->values()->toArray();
        $centro_custos = collect($result)->unique('centro_custo')->pluck('centro_custo')->sort()->values()->toArray();
        $funcao = collect($result)->unique('funcao')->pluck('funcao')->sort()->values()->toArray();

        if ($request->filled('campoBusca')) {
            $result = collect($result)->filter(function ($item) use ($request) {
                return stripos($item['nome'], $request->campoBusca) !== false;
            })->values();
        }

        if ($request->filled('campoCargo')) {
            $result = collect($result)->filter(function ($item) use ($request) {
                return stripos($item['cargo'], $request->campoCargo) !== false;
            })->values();
        }

        if ($request->filled('campoSituacao')) {
            $result = collect($result)->filter(function ($item) use ($request) {
                foreach ($item['todos_periodos'] as $periodo) {
                    if ($periodo['status_ferias'] === $request->campoSituacao) {
                        return true;
                    }
                }
                return false;
            })->values();
        }

        if ($request->filled('campoCentroCusto')) {
            $result = collect($result)->filter(function ($item) use ($request) {
                return stripos($item['centro_custo'], $request->campoCentroCusto) !== false;
            })->values();
        }

        return [
            'result' => $result,
            'lista_cargos' => $cargos,
            'lista_centro_custos' => $centro_custos,
            'lista_funcao' => $funcao,
            'total' => count($result)
        ];

    }

    public function show(Request $request)
    {
        if ($request->filled('periodo_range') && $request->filled('tipo') && $request->tipo == 'data') {
            $periodo = explode(' até ', $request->periodo_range);
            $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
            $dataFim = new DataHora($periodo[1] . ' 23:59:59');
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
                $queryResult->where('data_saida', '>=', $dataInicio->dataHoraInsert())
                    ->where('data_saida', '<=', $dataFim->dataHoraInsert());
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
            $centro_custo = 'NÃO INFORMADO';

            if (!is_null($ferias['admissao']['centro_custo_id'])) {
                $centro_custo = $ferias['admissao']['centro_custo']['label'];
            }

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
                'centro_custo' => $centro_custo,
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
//        $linhas = [];
//        $chunks = $this->showVencimentoFerias($request)['result']->chunk(100);
//
//        $chunks->each(function ($rows) use (&$linhas) {
//            foreach ($rows as $row) {
//                $linhas[] = $row; // Adicionando cada linha ao array $linhas
//            }
//        });
//
//
//        dd($linhas);
        $nameArquivo = "vencimento_ferias_" . \Str::slug('Vencimento Ferias') . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
//        JobExportarExcel::dispatch(auth()->id(), "Vencimento Ferias", Redis::get($this->nomeRelatorio()), $nameArquivo);
        JobExportarExcel::dispatch(auth()->id(), "Vencimento Ferias", $this->showVencimentoFerias($request)['result'], $nameArquivo);
//        JobExportarExcel::dispatch(auth()->user(), $this->showVencimentoFerias($request)['result']);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);

    }

    public function nomeRelatorio()
    {
        $empresa_id = auth()->user()->empresa_id;
        return "relatorio_vencimento_ferias_{$empresa_id}";
    }
}
