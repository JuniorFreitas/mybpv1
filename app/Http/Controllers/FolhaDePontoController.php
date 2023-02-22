<?php

namespace App\Http\Controllers;

use App\Models\Admissao;
use App\Models\EmpresaEscala;
use App\Models\FeedbackCurriculo;
use App\Models\Feriado;
use App\Models\OcorrenciaJornada;
use App\Models\PontoEletronico;
use App\Models\User;
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
                        ->with(['FotoEntrada','FotoSaida'])
                        ->withTrashed()->orderBy('created_at');
                },
                'PeriodosEmAberto' => function ($q) {
                    $q->select(['id','ponto_id'])->with(['FotoEntrada','FotoSaida'])->withTrashed();
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

        $consulta = PontoEletronico::whereBetween('created_at', [$inicio->dataHoraInsert(), $fim->dataHoraInsert()])
            //$lista = PontoEletronico::whereBetween('created_at',[$inicio->dataHoraInsert(),$fim->dataHoraInsert()])
            ->whereFuncionarioId($funcionario->id)
            ->with([
                'Ocorrencia:id,descricao,trabalhado,conta_horas',
                'Periodos' => function ($q) {
                    $q->select(['id', 'ponto_id', 'entrada', 'saida'])->withTrashed()->orderBy('created_at');
                },
                'PeriodosEmAberto' => function ($q) {
                    $q->select(['id','ponto_id'])->withTrashed();
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
            ->orderBy('created_at');

        //Total horas normais
        $totalHorasNormais = clone $consulta;
        $totalHorasNoturnas = clone $consulta;
        $totalHorasExtra = clone $consulta;
        $totalHorasNegativas = clone $consulta;

        $totalHorasNormais = $totalHorasNormais->whereDoesntHave('PeriodosEmAberto')->sum('duracao_normal');
        $totalHorasNoturnas = $totalHorasNoturnas->whereDoesntHave('PeriodosEmAberto')->sum('duracao_noturna');
        $totalHorasExtra = $totalHorasExtra->whereDoesntHave('PeriodosEmAberto')->where('duracao_extra', '>', 0)->sum('duracao_extra');
        $totalHorasNegativas = abs($totalHorasNegativas->whereDoesntHave('PeriodosEmAberto')->where('duracao_extra', '<', 0)->sum('duracao_extra'));

        $dias = [];

        $qnt_dias = DataHora::diferencaDias($inicio->dataHoraInsert(), $fim->dataHoraInsert());
        foreach (range(0, $qnt_dias) as $d) {
            $dataD = new DataHora($inicio->dataHoraInsert());
            $dh = $dataD->addDia($d, true);
            $feriado = Feriado::whereData($dataD->dataInsert())->whereAtivo(true)->first(['descricao', 'id']);
            $ponto = $consulta->get()->where('dia', '=', $dh)->first();

            $dias[] = [
                'dia' => $dh,
                'diaSem' => $dataD->diaSemanaExtM(),
                'diaSemana' => substr($dataD->diaSemanaExtM(), 0, 3),
                'feriado' => $feriado,
                'ponto' => $ponto
            ];
        }

        $pdf = PDF::loadView('pdf.controle-ponto.folha-ponto.pdf', [
            'dados' => $funcionario,
            'lista' => $dias,
            'intervaloText' => $intervaloText,
            'totalHorasNormais' => PontoEletronico::formataTempo($totalHorasNormais),
            'totalHorasNoturnas' => PontoEletronico::formataTempo($totalHorasNoturnas),
            'totalHorasExtra' => PontoEletronico::formataTempo($totalHorasExtra),
            'totalHorasNegativas' => PontoEletronico::formataTempo($totalHorasNegativas),
            'saldoValor' => ($totalHorasExtra + $totalHorasNoturnas) - $totalHorasNegativas,
            'saldoDeHoras' => PontoEletronico::formataTempo(($totalHorasExtra + $totalHorasNoturnas) - $totalHorasNegativas),
            'escala' => $funcionario->EmpresaEscalas[0]

        ]);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("Folha de ponto" . STR::slug($funcionario->nome) . ".pdf");

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
}
