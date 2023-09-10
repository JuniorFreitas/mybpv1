<?php

namespace App\Http\Controllers;

use App\Exports\Entrevistas\parecerTestePraticoExport;
use App\Jobs\JobExportaExcel;
use App\Models\FeedbackCurriculo;
use App\Models\ParecerTestePratico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use MasterTag\DataHora;
use PDF;

class ParecerTestePraticoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.entrevistas.parecer_teste_pratico.index');
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
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados, [
            'fez_teste' => 'required',
            'parecer_final_teste' => 'required',
            'quem_entrevistou' => 'required|min:3'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar a entrevista',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                ParecerTestePratico::create($dados);
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error PARECER RH ROTA UPDATE:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}| Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ParecerTestePratico $parecerTestePratico
     * @return \Illuminate\Http\Response
     */
    public function show(ParecerTestePratico $parecerTestePratico)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ParecerTestePratico $parecerTestePratico
     * @return \Illuminate\Http\Response
     */
    public function edit(FeedbackCurriculo $parecerTestePratico)
    {
        $feedback = $parecerTestePratico->load(
            'parecerTeste',
            'parecerRh:feedback_id,tipo_entrevista',
            'Curriculo',
            'Curriculo.Formacao',
            'TelPrincipal',
            'VagaAberta.vagaSelecionada'
        );
        return response()->json($feedback, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ParecerTestePratico $parecerTestePratico
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ParecerTestePratico $parecerTestePratico)
    {
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados, [
            'fez_teste' => 'required',
            'parecer_final_teste' => 'required',
            'quem_entrevistou' => 'required|min:3'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar a entrevista',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $parecerTestePratico->update($dados);
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error PARECER RH ROTA UPDATE:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}| Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ParecerTestePratico $parecerTestePratico
     * @return \Illuminate\Http\Response
     */
    public function destroy(ParecerTestePratico $parecerTestePratico)
    {
        //
    }

    public function atualizar(Request $request)
    {

        $resultado = $this->filtro($request)->paginate($request->pages);


        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $resultado = FeedbackCurriculo::with(
            'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
            'Cliente:id,razao_social',
            'VagaAberta.vagaSelecionada',
            'parecerRh:feedback_id,nota',
            'parecerTecnica:feedback_id,nota',
            'parecerRota:feedback_id,tem_rota',
            'parecerTeste:id,feedback_id,nota_teste'
        )
            ->has('parecerRh')
            ->whereIn('selecionado', ['sim', 'standby'])->whereInteresse(true);

        $filtroPeriodo = $request->filtroPeriodo == 'true';
        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0]. ' 00:00:00');
            $dataFim = new DataHora($periodo[1]. ' 23:59:59');
            $resultado->whereHas('parecerRota', function ($q) use ($dataInicio, $dataFim) {
                $q->where('created_at', '>=', $dataInicio->dataHoraInsert())
                    ->where('created_at', '<=', $dataFim->dataHoraInsert());
            });
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoCliente')) {
            $resultado->whereClienteId($request->campoCliente);
        }

        if ($request->filled('campoVaga')) {
            $resultado->whereHas('VagaAberta', function ($query) use ($request) {
                $query->whereId($request->campoVaga);
            });
        }

        if ($request->filled('campoUf')) {
            $resultado->whereHas('Curriculo', function ($q) use ($request) {
                $q->whereUfVaga($request->campoUf);
            });
        }

        if ($request->filled('campoCPF')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->whereCpf($request->campoCPF);
            });
        }

        if ($request->filled('campoPcd')) {
            $campoPcd = $request->campoPcd == 'true' ? true : false;
            $resultado->whereHas('Curriculo', function ($query) use ($campoPcd) {
                $query->wherePcd($campoPcd);
            });
        }
        return $resultado->orderByDesc('created_at');
    }

    public function export(Request $request)
    {
        $resultado = $this->filtro($request)->get();

        $head = [
            "Nome",
            "Cargo",
            "CPF",
            "Parecer Nota",
            "Teste Prático Nota",
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $rows[] = [
                $row->Curriculo->nome,
                $row->vaga_aberta_municipio,
                $row->Curriculo->cpf,
                $row->parecerRh ? $row->parecerRh->nota : "sem nota",
                $row->parecerTeste ? $row->parecerTeste->nota_teste : "sem nota",
            ];
        }

        $nameArquivo = "parecer_teste_pratico" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Parecer - Teste - Prático", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

    public function getFichaPdf(Request $request)
    {
        $testePratico = ParecerTestePratico::find($request->id)->append('data_entrevista');
        $dados = $testePratico;
        $pdf = PDF::loadView('pdf.entrevista.entrevista_tecnica.ficha', compact('dados'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("parecer_teste_pratico" . Str::slug($testePratico->FeedbackCurriculo->Curriculo->nome) . ".pdf");
    }
}
