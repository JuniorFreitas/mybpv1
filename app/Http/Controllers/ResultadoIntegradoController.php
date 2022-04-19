<?php

namespace App\Http\Controllers;

use App\Mail\Entrevista\EnvioDocumentosMail;
use App\Models\Cliente;
use App\Models\FeedbackCurriculo;
use App\Models\ResultadoIntegrado;
use App\Models\SimuladoVaga;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Log;
use MasterTag\DataHora;
use PDF;

class ResultadoIntegradoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.entrevistas.resultado_integrado.index');
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $dados = $request->input();
        $dados['documentos_entregue_data'] = $dados['documentos_entregue'] ? $dados['documentos_entregue_data'] : null;
        $dados['encaminhado_exame_data'] = $dados['encaminhado_exame'] ? $dados['encaminhado_exame_data'] : null;
        $dados['encaminhado_treinamento_data'] = $dados['encaminhado_treinamento'] ? $dados['encaminhado_treinamento_data'] : null;

        $dadosValidados = \Validator::make($dados, [
            'documentos_entregue' => 'required',
            'encaminhado_exame' => 'required',
            'encaminhado_treinamento' => 'required',
            'responsavel_envio' => 'required|min:3'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar o resultado integrado',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                \DB::beginTransaction();
//                ResultadoIntegrado::create($dados);
                \DB::commit();
                $feedback = FeedbackCurriculo::whereId($dados['feedback_id'])->with('Curriculo')->first();
                \Mail::send(new EnvioDocumentosMail([$feedback]));

                return response()->json([], 201);
            } catch (\Exception $e) {
                \DB::rollBack();
                $msg = "erro STORE RESULTADO INTEGRADO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }

        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ResultadoIntegrado $resultadoIntegrado
     * @return \Illuminate\Http\Response
     */
    public function show(ResultadoIntegrado $resultadoIntegrado)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\FeedbackCurriculo $resultadoIntegrado
     * @return \Illuminate\Http\Response
     */
    public function edit(FeedbackCurriculo $resultadoIntegrado)
    {
        $feedback = $resultadoIntegrado; //FeedbackCurriculo

        $feedback->load('parecerRh.individualRh',
            'parecerRh.gestorRh',
            'parecerRh.entrevistaRh',
            'CertificadosNr',
            'CursosFormacoes',
            'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
            'Curriculo.Formacao',
            'TelPrincipal',
            'VagaAberta.vagaSelecionada',
            'Cliente:id,razao_social,cnpj,nome,cpf,area_id',
            'Cliente.Area',
            'ResultadoIntegrado'
        )->load(['Simulados' => function ($query) {
            $query->with('SimuladoVaga.Simulado');
        }]);

        $feedback->Curriculo->autocomplete_label_municipio_modal = $feedback->Curriculo->Cidade ? $feedback->Curriculo->Cidade->nome . ' - ' . $feedback->Curriculo->Cidade->uf : '';
        $feedback->Curriculo->autocomplete_label_municipio_modal_anterior = $feedback->Curriculo->Cidade ? $feedback->Curriculo->Cidade->nome . ' - ' . $feedback->Curriculo->Cidade->uf : '';

        $feedback->autocomplete_label_vaga_modal = $feedback->VagaAberta->vagaSelecionada ? $feedback->VagaAberta->vagaSelecionada->nome . ' - ' . $feedback->VagaAberta->Municipio->uf : '';
        $feedback->autocomplete_label_vaga_modal_anterior = $feedback->VagaAberta->vagaSelecionada ? $feedback->VagaAberta->vagaSelecionada->nome . ' - ' . $feedback->VagaAberta->Municipio->uf : '';

        $feedback->autocomplete_label_cliente_modal = $feedback->Cliente ? $feedback->Cliente->razao_social . ' | ' . $feedback->Cliente->cnpj : '';
        $feedback->autocomplete_label_cliente_modal_anterior = $feedback->Cliente ? $feedback->Cliente->razao_social . ' | ' . $feedback->Cliente->cnpj : '';

        $simulados = SimuladoVaga::whereVagaId($feedback->vaga_id)
            ->whereHas('Simulado', function ($q) {
                $q->whereAtivo(true);
            })->count();

        return response()->json(['feedback' => $feedback, 'provas' => $simulados], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ResultadoIntegrado $resultadoIntegrado
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ResultadoIntegrado $resultadoIntegrado)
    {
        $dados = $request->input();
        $dados['documentos_entregue_data'] = $dados['documentos_entregue'] ? $dados['documentos_entregue_data'] : null;
        $dados['encaminhado_exame_data'] = $dados['encaminhado_exame'] ? $dados['encaminhado_exame_data'] : null;
        $dados['encaminhado_treinamento_data'] = $dados['encaminhado_treinamento'] ? $dados['encaminhado_treinamento_data'] : null;

        $dadosValidados = \Validator::make($dados, [
            'documentos_entregue' => 'required',
            'encaminhado_exame' => 'required',
            'encaminhado_treinamento' => 'required',
            'responsavel_envio' => 'required|min:3'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar o resultado integrado',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                \DB::beginTransaction();
                $resultadoIntegrado->update($dados);
                \DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                \DB::rollBack();
                Log::debug("erro update RESULTADO INTEGRADO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ResultadoIntegrado $resultadoIntegrado
     * @return \Illuminate\Http\Response
     */
    public function destroy(ResultadoIntegrado $resultadoIntegrado)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $resultado = FeedbackCurriculo::with(
            'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
            'Cliente:id,razao_social,area_id',
            'VagaAberta.vagaSelecionada',
            'parecerRh:id,feedback_id,nota,created_at',
            'TelPrincipal',
            'ResultadoIntegrado')
            ->has('parecerRh')
//            ->has('parecerTecnica')
//            ->has('parecerRota')
//            ->has('parecerTeste')
            ->whereIn('selecionado', ['sim', 'standby'])->whereInteresse(true);

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;
        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
            $resultado->whereHas('parecerRh', function ($q) use ($dataInicio, $dataFim) {
                $q->where('created_at', '>=', $dataInicio->dataInsert())->where('created_at', '<=', $dataFim->dataInsert());
            });
        }

        if ($request->filled('campoCliente')) {
            $resultado->whereClienteId($request->campoCliente);
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoCPF')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->whereCpf($request->campoBusca);
            });
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

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['itens' => $resultado->items()]
        ]);
    }

    public function getFichaPdf(Request $request, FeedbackCurriculo $feedback)
    {
        $dados = $feedback;
        $pdf = PDF::loadView('pdf.resultado_integrado.ficha', compact('dados'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("resultado_integrado_" . STR::slug($dados->Curriculo->nome) . ".pdf");
    }
}
