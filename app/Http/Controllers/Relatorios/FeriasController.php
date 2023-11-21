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
use DB;
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

        $qntUltimosPeriodos = (int)$request->filled('qntUltimosPeriodos') ? $request->qntUltimosPeriodos : 3;
        // Subconsulta para identificar os admissao_id que têm pelo menos 2 registros
        $subQuery = DB::table('ferias_calculo_avos as ferias_calculo_avos')
            ->select('ferias_calculo_avos.admissao_id')
            ->join('admissoes', 'ferias_calculo_avos.admissao_id', '=', 'admissoes.id')
            ->where('ferias_calculo_avos.empresa_id', $empresa_id)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('demissaos')
                    ->whereColumn('demissaos.feedback_id', 'admissoes.feedback_id');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('afastamentos')
                    ->whereColumn('afastamentos.feedback_id', 'admissoes.feedback_id');
            })
            ->groupBy('ferias_calculo_avos.admissao_id')//            ->havingRaw('COUNT(ferias_calculo_avos.admissao_id) <= ' . $qntUltimosPeriodos)
        ;

        $result = DB::table('ferias_calculo_avos as ferias_calculo_avos')
            ->joinSub($subQuery, 'sub', 'ferias_calculo_avos.admissao_id', '=', 'sub.admissao_id')
            ->join('admissoes', 'ferias_calculo_avos.admissao_id', '=', 'admissoes.id')
            ->join('feedback_curriculos as fc', 'admissoes.feedback_id', '=', 'fc.id')
            ->join('curriculos as c', 'fc.curriculo_id', '=', 'c.id')
            ->join('periodos_aquisitivos as pa', 'ferias_calculo_avos.periodo_aquisitivo_id', '=', 'pa.id')
            ->leftJoin('centro_custos', 'admissoes.centro_custo_id', '=', 'centro_custos.id')
            ->leftJoin('centro_custo_filials', 'admissoes.centro_custo_filial_id', '=', 'centro_custo_filials.id')
            ->select([
                'ferias_calculo_avos.id as fca_id',
                'ferias_calculo_avos.total_avos as fca_total_avos',
                'ferias_calculo_avos.admissao_id as fca_admissao_id',
                'ferias_calculo_avos.historico as fca_historico',
                DB::raw('DATE_FORMAT(ferias_calculo_avos.ultima_atualizacao, "%d/%m/%Y") as ultima_atualizacao'),
                'c.nome as curriculo_nome',
                'ferias_calculo_avos.periodo_aquisitivo_id as fca_periodo_aquisitivo_id',
                DB::raw("IF(ferias_calculo_avos.total_avos <= 27.5, 'Saldo insuficiente', IF(ferias_calculo_avos.total_avos = 30, 'Disponivel', NULL)) as status_avos"),
                'admissoes.feedback_id',
                DB::raw('DATE_FORMAT(admissoes.data_admissao, "%d/%m/%Y") as data_admissao'),
                'admissoes.cargo',
                'admissoes.funcao as admissoes_funcao',
                'admissoes.centro_custo_id',
                'centro_custos.label as centro_custo_label',
                'pa.label as periodo_aquisitivo_label',
                'pa.ano_inicial as periodo_aquisitivo_ano_inicial',
            ])
            ->where(function ($query) use ($request) {
                $query->where('admissoes.deleted_at', null)
                    ->orWhere('fc.deleted_at', null);
            })
            ->whereIn('admissoes.status', [Admissao::STATUS_ADMISSAO_ADMITIDO])
            ->orderBy('admissoes.id', 'asc')
            ->orderBy('pa.ano_inicial', 'desc')
            ->get()->groupBy('fca_admissao_id')->values();

        $periodos = [];

        foreach ($result as $linha) {
            foreach ($linha->take($qntUltimosPeriodos) as $ll) {
                $ferias = DB::table('ferias')
                    ->join('admissoes', 'ferias.admissao_id', '=', 'admissoes.id')
                    ->leftJoin('users as gestor', 'ferias.gestor_aprovacao_id', '=', 'gestor.id')
                    ->leftJoin('users as rh', 'ferias.rh_aprovacao_id', '=', 'rh.id')
                    ->select([
                        'ferias.id as ferias_id',
                        'ferias.admissao_id',
                        'ferias.periodo_aquisitivo_id',
                        DB::raw('DATE_FORMAT(ferias.data_saida, "%d/%m/%Y") as data_saida'),
                        DB::raw('DATE_FORMAT(ferias.data_retorno, "%d/%m/%Y") as data_retorno'),
                        'ferias.status_ferias',
                        'ferias.aprovado_via_script',
                        'ferias.status_aprovacao_gestor',
                        'ferias.gestor_aprovacao_id',
                        'ferias.rh_aprovacao_id',
                        'gestor.nome as gestor_aprovacao_nome',
                        'rh.nome as rh_aprovacao_nome',
                        'ferias.dias_saldo',
                        'ferias.qnt_dias',
                        'ferias.qnt_faltas',
                        DB::raw("IF(ferias.qnt_faltas > 0, 'Sim', 'Não') as tem_faltas")
                    ])
                    ->where('admissao_id', $ll->fca_admissao_id)
                    ->where('periodo_aquisitivo_id', $ll->fca_periodo_aquisitivo_id)
                    ->where('ferias.deleted_at', null)
                    ->where('ferias.status_aprovacao_gestor', 'Aprovado')
                    ->first();

//                $status_ferias = $ll->status_avos == 'Disponivel' ?: 'Saldo insuficiente';
                $status_ferias = $ll->status_avos;

                if ($ferias) {
                    $ferias_avos = (float)$ll->fca_total_avos;
                    if ($ferias_avos < 30) {
                        $status_ferias = 'Saldo insuficiente';
                    } elseif ((int)$ll->periodo_aquisitivo_ano_inicial <= 2020) {
                        $status_ferias = 'Gozada';
                    } else {
                        $status_ferias = isset($ferias->status_ferias) ? ucfirst($ferias->status_ferias == 'aguardando' ? 'Solicitada' : $ferias->status_ferias) : 'Disponivel';
                        $status_ferias = $status_ferias == 'Gozando' ? 'Em férias' : $status_ferias;
                    }
                }


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
                    case 'Em férias':
                        $colorir = 'badge-dark';
                        break;
                    default:
                        $colorir = 'badge-white';
                        break;
                }

                $ultimo_dia_avo = collect(json_decode($ll->fca_historico))->sortByDesc('data_mes')->first()->data_mes;

                $atraso = Carbon::now()->diffInDays((new DataHora($ultimo_dia_avo))->dataInsert());
                $dias_atraso = 0;
                if ($atraso >= self::VINTEMESES && in_array($status_ferias, ['Disponivel'])) {
                    $colorir = 'badge-danger';
                    $dias_atraso = $atraso;
                }
                if ($atraso >= self::DEZOITOMESES && $atraso < self::VINTEMESES && in_array($status_ferias, ['Disponivel'])) {
                    $colorir = 'badge-warning';
                    $dias_atraso = $atraso;
                }

                if ($atraso < self::DEZOITOMESES && $atraso < self::VINTEMESES && in_array($status_ferias, ['Disponivel'])) {
                    $colorir = 'badge-soft-dark';
                    $dias_atraso = $atraso;
                }

                $periodos[] = $this->dto(
                    $ll->fca_id ?? null,
                    $ll->fca_total_avos ?? null,
                    $ll->fca_admissao_id ?? null,
                    $ll->curriculo_nome ?? null,
                    $ll->fca_periodo_aquisitivo_id ?? null,
                    $ll->status_avos ?? null,
                    $ll->feedback_id ?? null,
                    $ll->data_admissao ?? null,
                    $ll->cargo ?? null,
                    $ll->admissoes_funcao ?? null,
                    $ll->centro_custo_id ?? null,
                    $ll->centro_custo_label ?? null,
                    $ll->periodo_aquisitivo_label ?? null,
                    $ll->periodo_aquisitivo_ano_inicial ?? null,
                    $ferias->data_saida ?? null,
                    $ferias->data_retorno ?? null,
                    $status_ferias ?: "Saldo insuficiente",
                    $ferias->aprovado_via_script ?? null,
                    $ferias->status_aprovacao_gestor ?? null,
                    $ferias->gestor_aprovacao_id ?? null,
                    $ferias->rh_aprovacao_id ?? null,
                    $ferias->gestor_aprovacao_nome ?? null,
                    $ferias->rh_aprovacao_nome ?? null,
                    $ferias->dias_saldo ?? null,
                    $ferias->qnt_dias ?? null,
                    $ferias->qnt_faltas ?? null,
                    $ferias->tem_faltas ?? null,
                    $colorir ?? null,
                    in_array($status_ferias, ['Disponivel']) && $atraso > 0 ? Carbon::now()->subDays($atraso)->diffForHumans(null, true, false, 2) : 0,
                    $dias_atraso ?? 0,
                    $ll->ultima_atualizacao ?: null,
                    (bool)$ferias
                );

            }
        }

        $result = collect($periodos)->groupBy('fca_admissao_id')->values()->map(function ($item) {

            $tempo_atrasado = $item->sum('dias_atraso');
            $atraso = $tempo_atrasado;
            if ($tempo_atrasado) {
                $atraso = Carbon::now()->diffInDays((new DataHora($tempo_atrasado))->dataInsert());
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
//

            return [
                'nome' => $item[0]['curriculo_nome'],
                'cargo' => $item[0]['cargo'],
                'dias_atraso' => $item->sum('dias_atraso'),
                'tempo_atrasado' => Carbon::now()->subDays($item->sum('dias_atraso'))->diffForHumans(null, true, false, 2),
                'pintar' => $colorir,
//                'funcao' => $item[0]['funcao'],
                'data_admissao' => $item[0]['data_admissao'],
                'centro_custo' => !is_null($item[0]['centro_custo_id']) ? $item[0]['centro_custo_label'] : 'Não informado',
                'todos_periodos' => $item,
            ];
        })->sortByDesc('dias_atraso')->values();


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

    public function dto(
        int $fca_id, float $fca_total_avos, $fca_admissao_id, $curriculo_nome,
            $fca_periodo_aquisitivo_id, $status_avos, $feedback_id, $data_admissao,
            $cargo, $admissoes_funcao, $centro_custo_id, $centro_custo_label,
            $periodo_aquisitivo_label, $periodo_aquisitivo_ano_inicial, $data_saida,
            $data_retorno, $status_ferias, $aprovado_via_script, $status_aprovacao_gestor,
            $gestor_aprovacao_id, $rh_aprovacao_id, $gestor_aprovacao_nome, $rh_aprovacao_nome,
            $dias_saldo, $qnt_dias, $qnt_faltas, $tem_faltas, $colorir, $tempo_atrasado, $dias_atraso,
            $ultima_atualizacao, bool $tem_tb_ferias
    )
    {
        return [
            'fca_id' => $fca_id,
            'total_avos' => $fca_total_avos,
            'fca_admissao_id' => $fca_admissao_id,
            'curriculo_nome' => $curriculo_nome,
            'periodo_aquisitivo_id' => $fca_periodo_aquisitivo_id,
            'status_avos' => $status_avos,
            'feedback_id' => $feedback_id,
            'data_admissao' => $data_admissao,
            'cargo' => $cargo,
            'admissoes_funcao' => $admissoes_funcao,
            'centro_custo_id' => $centro_custo_id,
            'centro_custo_label' => $centro_custo_label,
            'periodo_aquisitivo' => $periodo_aquisitivo_label,
            'periodo_aquisitivo_ano_inicial' => $periodo_aquisitivo_ano_inicial,
            'data_saida' => $data_saida,
            'data_retorno' => $data_retorno,
            'status_ferias' => $status_ferias,
            'aprovado_via_script' => $aprovado_via_script,
            'status_aprovacao_gestor' => $status_aprovacao_gestor,
            'gestor_aprovacao_id' => $gestor_aprovacao_id,
            'rh_aprovacao_id' => $rh_aprovacao_id,
            'gestor_aprovacao_nome' => $gestor_aprovacao_nome,
            'rh_aprovacao_nome' => $rh_aprovacao_nome,
            'dias_saldo' => $dias_saldo,
            'qnt_dias' => $qnt_dias,
            'qnt_faltas' => $qnt_faltas,
            'tem_faltas' => $tem_faltas,
            'colorir' => $colorir,
            'tempo_atrasado' => $tempo_atrasado,
            'dias_atraso' => $dias_atraso,
            'ultima_atualizacao' => $ultima_atualizacao,
            'tem_tb_ferias' => $tem_tb_ferias
        ];
    }

    public function showVencimentoFerias2(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;

//        if (\Cache::has($this->nomeRelatorio())) {
//            $result = json_decode(\Cache::get($this->nomeRelatorio()), true);
//        } else {
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
            ->leftjoin('periodos_aquisitivos', 'ferias.periodo_aquisitivo_id', '=', 'periodos_aquisitivos.id')
            ->leftJoin('users as gestor', 'ferias.gestor_aprovacao_id', '=', 'gestor.id')
            ->leftJoin('users as rh', 'ferias.rh_aprovacao_id', '=', 'rh.id')
            ->where('feedback_curriculos.empresa_id', $empresa_id)
            ->whereNotIn('admissoes.feedback_id', function ($query) {
                $query->select('feedback_id')->from('demissaos');
            })
//            ->whereNotNull('ferias.gestor_aprovacao_id')
//            ->where('ferias.status_aprovacao_gestor', 'Aprovado')
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
            ->sortByDesc('dias_atraso')->values();
//            \Cache::set($this->nomeRelatorio(), json_encode($result), 60 * 24);
//            Redis::set($this->nomeRelatorio(), \Cache::get($this->nomeRelatorio()));
//        }

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
        $nameArquivo = "vencimento_ferias_" . \Str::slug('Vencimento Ferias') . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportarExcel::dispatch(auth()->id(), "Vencimento Ferias", $this->showVencimentoFerias($request)['result']->toArray(), $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

    public function nomeRelatorio()
    {
        $empresa_id = auth()->user()->empresa_id;
        return "relatorio_vencimento_ferias_{$empresa_id}";
    }
}
