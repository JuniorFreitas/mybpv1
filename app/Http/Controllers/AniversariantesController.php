<?php

namespace App\Http\Controllers;

use App\Jobs\JobAniversariantes;
use App\Jobs\JobExportaExcel;
use App\Jobs\JobExportaPdf;
use App\Models\Curriculo;
use App\Models\ParabensEnviado;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class AniversariantesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.administracao.aniversariantes.index');
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
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    public function show()
    {

    }

    public function enviaEmail(Request $request)
    {
//        dd($request->selecionados);

        $dados = [
            'selecionados' => $request->selecionados,
            'empresa_id' => auth()->user()->empresa_id
        ];

        foreach ($request->selecionados as $selecionado) {
            ParabensEnviado::withoutGlobalScopes()->create([
                'empresa_id' => auth()->user()->empresa_id,
                'status' => ParabensEnviado::STATUS_ENVIANDO,
                'curriculo_id' => $selecionado,
                'ano' => (int)date('Y'),
            ]);
        }

        JobAniversariantes::dispatch($dados);

        return response()->json('', 200);
    }

    public function atualizar(Request $request)
    {
        $this->authorize('administracao_aniversariantes');
        $dataHoje = new DataHora();
        $ano = $dataHoje->ano();
        $ano = intval($ano);

        $funcionarios = Curriculo::select(['id', 'nome', 'email', 'nascimento', 'rg', 'orgao_expeditor'])
            ->whereHas('FeedBack', function ($q) {
                $q->admitidos();
            })->whereRaw('month(nascimento) = month(now())')
            ->with('Parabens', function ($query) use ($ano) {
                $query->where('ano', $ano);
            })->orderByRaw('day(nascimento)')->get()
            ->map(function ($item) {
                $data_nascimento = new DataHora($item->nascimento);
                $dia_nascimento = $data_nascimento->dia();
                return [
                    'idade' => $item->idade,
                    'nome' => $item->nome,
                    'email' => $item->email,
                    'id' => $item->id,
                    'aniversario' => $data_nascimento->dia() . '/' . $data_nascimento->mes(),
                    'enviado' => $item->Parabens->status ?? 'Não',
                    'hoje' => date('d') == $dia_nascimento,
                ];
            });

        return response()->json(['dados' => $funcionarios], 200);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function relatorioIndex()
    {
        return view('g.relatorios.aniversariantes.index');
    }

    public function relatorioAtualizar(Request $request)
    {
        $this->authorize('relatorio_aniversariantes');
        $campoMes = (int)$request->campoMes;
        $dataHoje = new DataHora();
        $ano = $dataHoje->ano();
        $ano = intval($ano);

        $funcionarios = Curriculo::select(['id', 'nome', 'email', 'nascimento', 'rg', 'orgao_expeditor'])
            ->whereHas('FeedBack', function ($q) {
                $q->admitidos();
            })->whereRaw('month(nascimento) =' . $campoMes)
            ->with('Parabens', function ($query) use ($ano) {
                $query->where('ano', $ano);
            })->orderByRaw('day(nascimento)')->get()->map(function ($item) {
                $data_nascimento = new DataHora($item->nascimento);
                $dia_nascimento = $data_nascimento->dia();
                $mes_nascimento = $data_nascimento->mes();
                return [
                    'idade' => $item->idade,
                    'nome' => $item->nome,
                    'email' => $item->email,
                    'id' => $item->id,
                    'aniversario' => $data_nascimento->dia() . '/' . $data_nascimento->mes(),
                    'enviado' => $item->Parabens->status ?? 'Não',
                    'hoje' => date('d') == $dia_nascimento && date('m') == $mes_nascimento,
                ];
            });

        $dados = [
            'funcionarios' => $funcionarios,
            'lista_meses' => ParabensEnviado::LISTA_MESES,
        ];

        return response()->json(['dados' => $dados], 200);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exporRelatorioPdf(Request $request)
    {
        $campoMes = (int)$request->campoMes;
        $mes = ParabensEnviado::LISTA_MESES[$campoMes];
        $dataHoje = new DataHora();
        $ano = $dataHoje->ano();
        $ano = intval($ano);

        $funcionarios = Curriculo::select(['id', 'nome', 'email', 'nascimento', 'rg', 'orgao_expeditor'])->whereHas('FeedBack', function ($q) {
            $q->admitidos();
        })->whereRaw('month(nascimento) =' . $campoMes)
            ->with('Parabens', function ($query) use ($ano) {
                $query->where('ano', $ano);
            })->orderByRaw('day(nascimento)')->get()->map(function ($item) {
                $data_nascimento = new DataHora($item->nascimento);
                return [
                    'nome' => $item->nome,
                    'email' => $item->email,
                    'aniversario' => $data_nascimento->dia() . '/' . $data_nascimento->mes()
                ];
            });

        $dados = [
            'rows' => $funcionarios,
            'mes' => $mes
        ];

        $view = 'pdf.relatorio.aniversariantes.aniversariantes';
        $nameArquivo = "relatorio_aniversariantes_" . (new DataHora())->nomeUnico() . ".pdf";

        $usuario['empresa_id'] = auth()->user()->empresa_id;
        $usuario['id'] = auth()->user()->id;
        $usuario['nome'] = auth()->user()->nome;
        $usuario['logo'] = null;
        $usuario['razao_social'] = auth()->user()->DadosEmpresa->razao_social;
        $usuario['endereco'] = auth()->user()->Empresa->endereco_completo;
        $usuario['cnpj'] = auth()->user()->DadosEmpresa->cnpj;
        if (count(auth()->user()->ClientesLogo) > 0) {
            $usuario['logo'] = auth()->user()->ClientesLogo[0]->urlThumb;
        }

        JobExportaPdf::dispatch($usuario, "Relatório - Aniversariantes de " . $mes . " (PDF)", $dados, $nameArquivo, $view);
        return response()->json(['msg' => 'Estamos gerando seu arquivo pdf, assim que finalizado você será notificado.']);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exporRelatorioExcel(Request $request)
    {
        $head = [
            "Nome",
            "E-mail",
            "Data"
        ];

        $campoMes = (int)$request->campoMes;
        $mes = ParabensEnviado::LISTA_MESES[$campoMes];
        $dataHoje = new DataHora();
        $ano = $dataHoje->ano();
        $ano = intval($ano);

        $funcionarios = Curriculo::select(['id', 'nome', 'email', 'nascimento', 'rg', 'orgao_expeditor'])->whereHas('FeedBack', function ($q) {
            $q->admitidos();
        })->whereRaw('month(nascimento) =' . $campoMes)
            ->with('Parabens', function ($query) use ($ano) {
                $query->where('ano', $ano);
            })->orderByRaw('day(nascimento)')->get()->map(function ($item) {
                $data_nascimento = new DataHora($item->nascimento);
                return [
                    'nome' => $item->nome,
                    'email' => $item->email,
                    'aniversario' => $data_nascimento->dia() . '/' . $data_nascimento->mes()
                ];
            });

        $rows = [];
        foreach ($funcionarios as $row) {
            $rows[] = [
                'nome' => $row['nome'],
                'email' => $row['email'],
                'aniversario' => $row['aniversario']
            ];
        }

        $nameArquivo = "aniversariantes_" . mb_strtolower($mes) . '_' . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Aniversarientes de " . $mes, $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }
}
