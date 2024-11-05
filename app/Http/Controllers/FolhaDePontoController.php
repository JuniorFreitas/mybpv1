<?php

namespace App\Http\Controllers;

use App\Models\Curriculo;
use App\Models\EmpresaConfig;
use App\Models\EmpresaEscala;
use App\Models\FeedbackCurriculo;
use App\Models\Feriado;
use App\Models\OcorrenciaJornada;
use App\Models\PontoEletronico;
use App\Models\Sistema;
use App\Models\User;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use PDF;

class FolhaDePontoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /*$lista = PontoEletronico::get();
        foreach ($lista as $ponto){
            $ponto->duracao_extra = $ponto->horas_extra;
            $ponto->duracao_normal = $ponto->horas_normal;
            $ponto->duracao_noturna = $ponto->horas_noturna;
            $ponto->save();
        }*/
        return view('g.controle-ponto.folha-ponto.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {

    }

    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(User $user)
    {
        $funcionario = User::select(['id', 'nome', 'empresa_id', 'login as email'])
            ->whereEmpresaId(auth()->user()->empresa_id)
            ->whereId($user->id)
            ->first();

        if (!$funcionario) {
            return response()->json(['msg' => 'Funcionário não encontrado'], 400);
        }

        $feedbackCurriculo = FeedbackCurriculo::whereCurriculoId($funcionario->id)
            ->whereEmpresaId($funcionario->empresa_id)
            ->first();

        $dadosDoFuncionario = [
            'nome' => $funcionario->nome,
            'matricula' => 'NÃO INFORMADO',
            'data_admissao' => 'NÃO INFORMADO',
            'cargo' => 'NÃO INFORMADO',
            'centro_custo' => 'NÃO INFORMADO',
            'area' => 'NÃO INFORMADO',
            'u_token' => ''
        ];

        if ($feedbackCurriculo) {
            $admissao = $feedbackCurriculo->Admissao;
            $dadosDoFuncionario = [
                'nome' => $funcionario->nome,
                'matricula' => $admissao->matricula ?: "NÃO INFORMADO",
                'data_admissao' => $admissao->data_admissao,
                'cargo' => $admissao->cargo,
                'centro_custo' => $admissao->CentroCusto ? $admissao->CentroCusto->label : "NÃO INFORMADO",
                'area' => $admissao->AreaEtiqueta ? $admissao->AreaEtiqueta->label : "NÃO INFORMADO",
                'u_token' => \Crypt::encrypt($funcionario->id)
            ];
        }

        return $funcionario->dados_funcionario = $dadosDoFuncionario;

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function buscarFrequencia(Request $request, User $user)
    {
        $funcionario = User::whereEmpresaId(auth()->user()->empresa_id)->whereId($user->id)->first();

        if (!$funcionario) {
            return response()->json(['msg' => 'Funcionário não encontrado'], 400);
        }

        $intervalo = explode(" até ", $request->intervalo);
        $inicio = new DataHora($intervalo[0] . " 00:00:00");
        $fim = new DataHora($intervalo[1] . " 23:59:59");

        $lista = PontoEletronico::whereBetween('created_at', [$inicio->dataHoraInsert(), $fim->dataHoraInsert()])
            //$lista = PontoEletronico::whereBetween('created_at',[$inicio->dataHoraInsert(),$fim->dataHoraInsert()])
            ->whereFuncionarioId($funcionario->id)
            ->with([
                'Ocorrencia:id,descricao,trabalhado,conta_horas',
                'Periodos' => function ($q) {
                    $q->select(['id', 'arquivo_id_entrada', 'arquivo_id_saida', 'ponto_id', 'entrada', 'saida'])
                        ->with(['FotoEntrada', 'FotoSaida'])
                        ->withTrashed()->orderBy('created_at');
                },
                'PeriodosEmAberto' => function ($q) {
                    $q->select(['id', 'ponto_id'])->with(['FotoEntrada', 'FotoSaida'])->withTrashed();
                },
                'Jornada' => function ($q) {
                    $q->withTrashed()
                        ->with([
                            'Ocorrencia:id,descricao,trabalhado,conta_horas',
                            'Escala' => function ($q) {
                                $q->withTrashed();
                            },
                        ]);
                },
            ])
//            ->with([
//                'PeriodosEmAberto:id,ponto_id',
//                'Jornada.Ocorrencia:id,descricao,trabalhado,conta_horas',
//                'Jornada.Escala',
//                'Ocorrencia:id,descricao,trabalhado,conta_horas',
//                'Periodos' => function ($q) {
//                    $q->orderBy('created_at')->select(['id', 'ponto_id', 'entrada', 'saida']);
//                },
//            ])
            ->orderBy('created_at')
            ->get();

        $dias = [];

        $qnt_dias = DataHora::diferencaDias($inicio->dataHoraInsert(), $fim->dataHoraInsert());
        foreach (range(0, $qnt_dias) as $d) {
            $dataD = new DataHora($inicio->dataHoraInsert());
            $dh = $dataD->addDia($d, true);
            $feriado = Feriado::whereData($dataD->dataInsert())->whereAtivo(true)->first(['descricao', 'id']);
            $ponto = $lista->where('dia', '=', $dh)->first();

            $dias[] = [
                'dia' => $dh,
                'diaSem' => $dataD->diaSemanaExtM(),
                'diaSemana' => substr($dataD->diaSemanaExtM(), 0, 3),
                'feriado' => $feriado,
                'ponto' => $ponto
            ];
        }

        return response()->json([
            'pontos' => $lista,
            'ocorrencia_falta' => OcorrenciaJornada::FALTA,
            'calendario' => $dias,
        ], 200);
    }

    // Função para formatar valores menores que 10 com zero à esquerda
    public function formataTempo($value)
    {
        return str_pad($value, 2, '0', STR_PAD_LEFT);
    }

// Função para formatar uma quantidade de minutos em horas e minutos
    function formataHoras($quantidade_minutos)
    {
        // Retorna "00h:00m" se a quantidade de minutos for zero
        if ($quantidade_minutos === 0) {
            return "00h:00m";
        }

        // Cria o intervalo de tempo com base na quantidade de minutos
        $intervalo = CarbonInterval::minutes(abs($quantidade_minutos));

        // Calcula as horas totais e minutos restantes
        $horas = floor($intervalo->totalHours); // Total de horas (inclui dias convertidos para horas)
        $minutos = $intervalo->minutes % 60;    // Minutos restantes entre 0 e 59

        // Retorna o resultado no formato "Xh:Ym" com zero à esquerda quando necessário
        return sprintf("%02dh:%02dm", $horas, $minutos);
    }

    public function imprimir(Request $request, $user)
    {
        $u_token = \Crypt::decrypt($user);
        $funcionario = User::whereEmpresaId(auth()->user()->empresa_id)->whereId($u_token)->first();

        if (!$funcionario) {
            return response()->json(['msg' => 'Funcionário não encontrado'], 400);
        }

        $intervaloText = $request->intervalo;
        $intervalo = explode(" até ", $request->intervalo);
        $inicio = new DataHora($intervalo[0] . " 00:00:00");
        $fim = new DataHora($intervalo[1] . " 23:59:59");

        $lista = PontoEletronico::whereBetween('created_at', [$inicio->dataHoraInsert(), $fim->dataHoraInsert()])
            ->whereFuncionarioId($funcionario->id)
            ->with([
                'Ocorrencia:id,descricao,trabalhado,conta_horas',
                'Periodos' => function ($q) {
                    $q->select(['id', 'arquivo_id_entrada', 'arquivo_id_saida', 'ponto_id', 'entrada', 'saida'])
                        ->withTrashed()->orderBy('created_at');
                },
                'PeriodosEmAberto' => function ($q) {
                    $q->select(['id', 'ponto_id'])->with(['FotoEntrada', 'FotoSaida'])->withTrashed();
                },
                'Jornada' => function ($q) {
                    $q->withTrashed()
                        ->with([
                            'Ocorrencia:id,descricao,trabalhado,conta_horas',
                            'Escala' => function ($q) {
                                $q->withTrashed();
                            },
                        ]);
                },
            ])
            ->orderBy('created_at')
            ->get();

        $dias = [];
        $qnt_dias = DataHora::diferencaDias($inicio->dataHoraInsert(), $fim->dataHoraInsert());

        foreach (range(0, $qnt_dias) as $d) {
            $dataD = new DataHora($inicio->dataHoraInsert());
            $dh = $dataD->addDia($d, true);
            $feriado = Feriado::whereData($dataD->dataInsert())->whereAtivo(true)->first(['descricao', 'id']);
            $ponto = $lista->where('dia', '=', $dh)->first();

            $dias[] = [
                'dia' => $dh,
                'diaSem' => $dataD->diaSemanaExtM(),
                'diaSemana' => substr($dataD->diaSemanaExtM(), 0, 3),
                'feriado' => $feriado,
                'ponto' => $ponto
            ];
        }

        $quantidadeFaltas = $lista->filter(function ($ponto) {
            return $ponto->ocorrencia_id === OcorrenciaJornada::FALTA;
        })->count();

        $totalHorasNormais = $lista->filter(function ($ponto) {
            return empty($ponto->periodos_em_aberto);
        })->sum('duracao_normal');

        $totalHorasNoturnas = $lista->filter(function ($ponto) {
            return empty($ponto->periodos_em_aberto);
        })->sum('duracao_noturna');

        $totalHorasExtra = $lista->filter(function ($ponto) {
            return empty($ponto->periodos_em_aberto);
        })->sum(function ($ponto) {
            return $ponto->duracao_extra > 0 ? $ponto->duracao_extra : 0;
        });

        $totalHorasNegativas = $lista->filter(function ($ponto) {
            return empty($ponto->periodos_em_aberto);
        })->sum(function ($ponto) {
            return $ponto->duracao_extra < 0 ? abs($ponto->duracao_extra) : 0;
        });

        $saldoHoras = ($totalHorasExtra + $totalHorasNoturnas) - $totalHorasNegativas;

        $escalasUnicas = collect($lista)
            ->pluck('jornada.escala')              // Pega todas as escalas dos registros
            ->unique('id')                         // Filtra por ID para remover duplicatas
            ->filter()                             // Remove quaisquer valores nulos
            ->values();                            // Reorganiza os índices

        $curriculo = \DB::table('curriculos')->select(['id', 'cpf'])->where('id', $funcionario->id)->first();
        $cpf = $curriculo && $curriculo->cpf ? $curriculo->cpf : "";

        $arrayDados = [
            'dados' => $funcionario,
            'lista' => $lista,
            'ocorrencia_falta' => OcorrenciaJornada::FALTA,
            'calendario' => $dias,
            'quantidade_faltas' => $quantidadeFaltas,
            'totalHorasNormais' => $this->formataHoras($totalHorasNormais),
            'totalHorasNoturnas' => $this->formataHoras($totalHorasNoturnas),
            'totalHorasExtra' => $this->formataHoras($totalHorasExtra),
            'totalHorasNegativas' => $this->formataHoras($totalHorasNegativas),
            'saldoValor' => ($totalHorasExtra + $totalHorasNoturnas) - $totalHorasNegativas,
            'saldoDeHoras' => $this->formataHoras($saldoHoras),
            'razao_social' => auth()->user()->DadosEmpresa->razao_social,
            'intervaloText' => $intervaloText,
            'escala' => $escalasUnicas,
            'multi_escalas' => $escalasUnicas->count() > 1,
            'cpf' => $cpf
        ];

        $pdf = PDF::loadView('pdf.controle-ponto.folha-ponto.pdf2', $arrayDados);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("Folha de ponto" . Str::slug($funcionario->nome) . ".pdf");

    }

    public function atualizarLista(Request $request)
    {
        $hoje = new DataHora();
        $porPagina = $request->get('porPagina');

        //Lista de funcionarios da empresa
//        $resultado = auth()->user()->Empresa->EmpresaFuncionarios()->whereAtivo(true)->whereTemp(false)->has('EscalasFuncionario');

//        $resultado = auth()->user()->Empresa->EmpresaFuncionarios()->whereAtivo(true)->whereTemp(false)
//            ->has('EscalasFuncionario')
//            ->whereHas('Feedback', function ($q) use ($request) {
//                if ($request->status == 'admitidos'){
//                    $q->admitidos();
//                }else{
//                    $q->demitidos();
//                }
//            });

        if (auth()->user()->can('controle_ponto_folha-ponto_adm')) {
            $user = auth()->user()->Empresa->EmpresaFuncionarios()->whereAtivo(true)->whereTemp(false);
        } else {
            $user = User::whereId(auth()->id())->whereAtivo(true)->whereTemp(false);
        }

        $resultado = $user->has('EscalasFuncionario')
            ->whereHas('Feedback', function ($q) use ($request) {
                if ($request->status == 'admitidos') {
                    $q->admitidos();
                } else {
                    $q->demitidos();
                }
            });

        if ($request->filled('funcionario_id')) {
            $resultado->whereId($request->funcionario_id);
        }

        if ($request->filled('escala_id')) {
            $resultado->whereHas('EscalasFuncionario', function ($q) use ($request) {
                $q->whereEscalaId($request->escala_id);
            });
        }

        if ($request->filled('funcionarioNome')) {
            $resultado = $resultado->where('nome', 'like', '%' . $request->funcionarioNome . '%');
        }

        $resultado->orderBy('nome'); // senao busca tudo


        $resultado->with([
            'EscalasFuncionario',
        ]);

        $resultado = $resultado->paginate($porPagina);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'todas_escalas' => EmpresaEscala::select(['id', 'empresa_id', 'descricao'])->get(),
                'controle_ponto_adm' => (auth()->user()->can('controle_ponto_folha-ponto_adm'))
            ],
        ]);
    }

    public function relatoriosintetico(Request $request)
    {
        return view('g.controle-ponto.relatorio-sintetico.index');
    }

    public function relatoriosinteticoexportacao(Request $request)
    {

        $empresa_id = auth()->user()->empresa_id;
        $usuario_id = auth()->id();
        $request->status = 'admitidos';


        $dia_inicial_frequencia = EmpresaConfig::whereEmpresaId($empresa_id)->first(['dia_nova_frequencia'])->dia_nova_frequencia;

        $correlation_id = "{$empresa_id}_{$usuario_id}_{$dia_inicial_frequencia}_" . date('m_Y') . "_{$request->status}_{$request->centro_custo_filial_id}";

        $dataInicialMes = new DataHora("{$dia_inicial_frequencia}/" . $request->mes . "/" . $request->ano . " 00:00:00");
        $dataFimMes = clone $dataInicialMes;
        $dataFimMes->addDia(30);

        $request->intervalo = "{$dataInicialMes->dataCompleta()} até {$dataFimMes->dataCompleta()}";
        $intervalo = explode(" até ", $request->intervalo);
        $inicio = new DataHora($intervalo[0] . " 00:00:00");
        $fim = new DataHora($intervalo[1] . " 23:59:59");

        $dadosDaEmpresa = !is_null($request->centro_custo_filial_id) ? Sistema::getFilial($empresa_id, $request->centro_custo_filial_id) : Sistema::getEmpresa($empresa_id);

        $funcionarios = PontoEletronico::selectRaw('funcionario_id, COUNT(funcionario_id) as funcionario_count')
            ->whereBetween('created_at', [$inicio->dataHoraInsert(), $fim->dataHoraInsert()])
            ->where('empresa_id', $empresa_id)
            ->whereHas('Funcionario.Feedback', function ($q) use ($request) {
                if ($request->filled('status')) {
                    $q->when($request->status == 'admitidos', function ($query) {
                        $query->admitidos();
                    }, function ($query) {
                        $query->demitidos();
                    });
                }
                $q->whereHas('Admissao', function ($q) use ($request) {
                    $q->when($request->filled('centro_custo_filial_id'), function ($query) use ($request) {
                        $query->where('centro_custo_filial_id', $request->centro_custo_filial_id);
                    });
                });
            })
            ->groupBy('funcionario_id')
            ->havingRaw('funcionario_count > 1')
            ->orderBy('funcionario_id')
            ->pluck('funcionario_id');

        $ll = [];

        foreach ($funcionarios as $funcionario_id) {
            $consulta = PontoEletronico::whereBetween('created_at', [$inicio->dataHoraInsert(), $fim->dataHoraInsert()])
                ->whereFuncionarioId($funcionario_id)
                ->orderBy('created_at');

            //Total horas normais
            $totalFaltas = clone $consulta;
            $totalDiasTrabalhados = clone $consulta;
            $totalHorasNormais = clone $consulta;
            $totalHorasNoturnas = clone $consulta;
            $totalHorasExtra = clone $consulta;
            $totalHorasNegativas = clone $consulta;

            $totalHorasNormais = $totalHorasNormais->whereDoesntHave('PeriodosEmAberto')->sum('duracao_normal');
            $totalHorasNoturnas = $totalHorasNoturnas->whereDoesntHave('PeriodosEmAberto')->sum('duracao_noturna');
            $totalHorasExtra = $totalHorasExtra->whereDoesntHave('PeriodosEmAberto')->where('duracao_extra', '>', 0)->sum('duracao_extra');
            $totalHorasNegativas = abs($totalHorasNegativas->whereDoesntHave('PeriodosEmAberto')->where('duracao_extra', '<', 0)->sum('duracao_extra'));

            $dadosDoFuncionario = Sistema::getColaboradorDados($funcionario_id, $empresa_id);

            $ll[] = [
                'funcionario_id' => (int)$funcionario_id,
                'empresa_id' => (int)$empresa_id,
                'funcionario' => $dadosDoFuncionario,
                'total_faltas' => $totalFaltas->whereOcorrenciaId(OcorrenciaJornada::FALTA)->count(),
                'total_dias_trabalhados' => $totalDiasTrabalhados->whereOcorrenciaId(OcorrenciaJornada::DIA_TRABALHADO)->count(),
                'total_horas_normais' => PontoEletronico::formataTempo($totalHorasNormais),
                'total_horas_noturnas' => PontoEletronico::formataTempo($totalHorasNoturnas),
                'total_horas_extra' => PontoEletronico::formataTempo($totalHorasExtra),
                'total_horas_negativas' => PontoEletronico::formataTempo($totalHorasNegativas),
                'saldo_horas' => PontoEletronico::formataTempo(($totalHorasExtra + $totalHorasNoturnas) - $totalHorasNegativas),
            ];
        }

        $dados = [
            'periodo' => $inicio->dataCompleta() . " até " . $fim->dataCompleta(),
            'dados_empresa' => $dadosDaEmpresa,
            'dados_ponto' => collect($ll)->sortBy('funcionario.nome')->values()->all(),
            'total_funcionarios' => count($ll),
            'correlation_id' => $correlation_id,
            'solicitante' => User::select('nome')->find(auth()->id())->nome
        ];

        return PDF::loadView('pdf.controle-ponto.folha-ponto.relatoriosintetico', compact('dados'))->setPaper('a4', 'landscape')->stream('relatorio_sintetico.pdf');

//        return view('pdf.controle-ponto.folha-ponto.relatoriosintetico', compact('dados'));
    }
}
