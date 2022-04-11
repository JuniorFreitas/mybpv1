<?php

namespace App\Http\Controllers;

use App\Exports\Entrevistas\parecerTransporteExport;
use App\Models\FeedbackCurriculo;
use App\Models\ParecerRota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use MasterTag\DataHora;
use PDF;

class ParecerRotaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.entrevistas.parecer_rota.index');
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
            'tem_rota' => 'required',
//            'pega_onibus' => 'required',
            'vale_transporte' => 'required',
            'rota_atende' => 'required',
            'rota_tipo' => 'required',
            'quem_entrevistou' => 'required|min:3'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao salvar a entrevista',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                ParecerRota::create($dados);
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error PARECER RH ROTA STORE:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}| Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ParecerRota $parecerRota
     * @return \Illuminate\Http\Response
     */
    public function show(ParecerRota $parecerRota)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ParecerRota $parecerRota
     * @return \Illuminate\Http\Response
     */
    public function edit(FeedbackCurriculo $parecerRota)
    {
        $feedback = $parecerRota->load('parecerRota',
            'parecerRh:feedback_id,tipo_entrevista',
            'Curriculo',
            'Curriculo.Formacao',
            'TelPrincipal',
            'vagaSelecionada'
        );

        return response()->json($feedback, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ParecerRota $parecerRota
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ParecerRota $parecerRota)
    {
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados, [
            'tem_rota' => 'required',
            'pega_onibus' => 'required',
            'vale_transporte' => 'required',
            'rota_atende' => 'required',
            'rota_tipo' => 'required',
            'quem_entrevistou' => 'required|min:3'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar a entrevista',
                'erros' => $dadosValidados->errors()
            ], 400);
        }else{
            try {
                DB::beginTransaction();
                $parecerRota->update($dados);
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
     * @param \App\Models\ParecerRota $parecerRota
     * @return \Illuminate\Http\Response
     */
    public function destroy(ParecerRota $parecerRota)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $resultado = FeedbackCurriculo::with(
            'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
            'Cliente:id,razao_social',
            'VagaAberta.VagaSelecionada',
            'parecerRh',
            'parecerTecnica',
            'parecerRota',
            'parecerTeste')
            ->has('parecerRh')
            ->whereIn('selecionado', ['sim', 'standby'])->whereInteresse(true);

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;
        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
            $resultado->whereHas('parecerRota', function ($q) use ($dataInicio, $dataFim) {
                $q->where('created_at', '>=', $dataInicio->dataInsert())->where('created_at', '<=', $dataFim->dataInsert());
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
                $query->whereCpf($request->campoBusca);
            });
        }

        if ($request->filled('campoPcd')) {
            $campoPcd = $request->campoPcd == 'true' ? true : false;
            $resultado->whereHas('Curriculo', function ($query) use ($campoPcd) {
                $query->wherePcd($campoPcd);
            });
        }

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
            ]
        ]);
    }

    public function export(Request $request)
    {
        $resultado = FeedbackCurriculo::whereInteresse(true)
            ->whereIn('selecionado', ['sim', 'standby'])
            ->has('parecerRota');

        if ($request->selecionados) {
            $resultado->whereIn('id', $request->selecionados);
        } else {

            if ($request->filled('campoCliente')) {
                $resultado->whereClienteId($request->campoCliente);
            }

            if ($request->filled('campoVaga')) {
                $resultado->whereHas('VagaSelecionada', function ($query) use ($request) {
                    $query->whereId($request->campoVaga);
                });
            }

            if ($request->filled('campoUf')) {
                $resultado->whereHas('Curriculo', function ($q) use ($request) {
                    $q->whereUfVaga($request->campoUf);
                });
            }

            if ($request->filled('campoPcd')) {
                $campoPcd = $request->campoPcd == 'true' ? true : false;
                $resultado->whereHas('Curriculo', function ($query) use ($campoPcd) {
                    $query->wherePcd($campoPcd);
                });
            }
        }

        $resultado = $resultado->orderByDesc('created_at')->get();

        return Excel::download(new parecerTransporteExport($resultado), 'parecer_rota_transportes' . (new DataHora())->nomeUnico() . '.xlsx');
    }

    public function getFichaPdf(Request $request)
    {
        $parecer_rota = ParecerRota::find($request->id)->append('data_entrevista');
        $dados = $parecer_rota;
        $pdf = PDF::loadView('pdf.entrevista.parecer_rota.ficha', compact('dados'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("parecer_rota" . Str::slug($parecer_rota->FeedbackCurriculo->Curriculo->nome) . ".pdf");
    }
}
