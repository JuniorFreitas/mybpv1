<?php

namespace App\Http\Controllers;

use App\Models\Admissao;
use App\Models\EmpresaEscala;
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
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, User $user)
    {
        $funcionario = User::whereEmpresaId(auth()->user()->empresa_id)->whereId($user->id)->first();
        if (!$funcionario) {
            return response()->json(['msg' => 'Funcionário não encontrado'], 400);
        }
        return $funcionario;
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
                'PeriodosEmAberto:id,ponto_id',
                'Jornada.Ocorrencia:id,descricao,trabalhado,conta_horas',
                'Jornada.Escala',
                'Ocorrencia:id,descricao,trabalhado,conta_horas',
                'Periodos' => function ($q) {
                    $q->orderBy('created_at')->select(['id', 'ponto_id', 'entrada', 'saida']);
                },
            ])
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'pontos' => $lista,
            'ocorrencia_falta' => OcorrenciaJornada::FALTA,
        ], 200);
    }

    public function imprimir(Request $request, User $user)
    {

        $funcionario = User::whereEmpresaId(auth()->user()->empresa_id)->whereId($user->id)->first();
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
                'PeriodosEmAberto:id,ponto_id',
                'Jornada.Ocorrencia:id,descricao,trabalhado,conta_horas',
                'Jornada.Escala',
                'Ocorrencia:id,descricao,trabalhado,conta_horas',
                'Periodos' => function ($q) {
                    $q->orderBy('created_at')->select(['id', 'ponto_id', 'entrada', 'saida']);
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

        $dados = $user;
        $pdf = PDF::loadView('pdf.controle-ponto.folha-ponto.pdf', [
            'dados' => $dados,
            'lista' => $consulta->get(),
            'intervaloText' => $intervaloText,
            'totalHorasNormais' => PontoEletronico::formataTempo($totalHorasNormais),
            'totalHorasNoturnas' => PontoEletronico::formataTempo($totalHorasNoturnas),
            'totalHorasExtra' => PontoEletronico::formataTempo($totalHorasExtra),
            'totalHorasNegativas' => PontoEletronico::formataTempo($totalHorasNegativas),
            'saldoValor' => ($totalHorasExtra + $totalHorasNoturnas) - $totalHorasNegativas,
            'saldoDeHoras' => PontoEletronico::formataTempo(($totalHorasExtra + $totalHorasNoturnas) - $totalHorasNegativas),
            'escala' => $user->EmpresaEscalas[0]

        ]);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream("Folha de ponto" . STR::slug($dados->tipo == 'Pessoa Jurídica' ? $dados->razao_social : $dados->nome) . ".pdf");

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

        if(auth()->user()->can('controle_ponto_folha-ponto_adm')){
            $user = auth()->user()->Empresa->EmpresaFuncionarios()->whereAtivo(true)->whereTemp(false);
        }else{
            $user =  User::whereId(auth()->id())->whereAtivo(true)->whereTemp(false);
        }

        $resultado = $user->has('EscalasFuncionario')
            ->whereHas('Feedback', function ($q) use ($request) {
                if ($request->status == 'admitidos'){
                    $q->admitidos();
                }else{
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
                'todas_escalas' => EmpresaEscala::select(['id','empresa_id','descricao'])->get(),
                'controle_ponto_adm' => (auth()->user()->can('controle_ponto_folha-ponto_adm'))
            ],
        ]);
    }
}
