<?php

namespace App\Http\Controllers;

use App\Models\Simulado;
use App\Models\VagasAbertas;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class VagasAbertasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('vagas_abertas');
        return view('g.cadastros.vagas_abertas.index');
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
        $this->authorize('vagas_abertas_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dadosValidados = \Validator::make($dados, [
            'vaga_id' => 'required',
            'municipio_id' => 'required'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Vaga',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();

                $vagas_aberta = VagasAbertas::create($dados);

                if (isset($dados['simulados'])) {
                    foreach ($dados['simulados'] as $simulado) {


                        if ($simulado['tipo_prova'] == 'subjetiva') {
                            $simulado['online'] = false;
                            $simulado['duracao'] = 0;
                        } else {
                            $simulado['online'] = $simulado['online'] == 'true';
                        }

                        $simulado['data_inicio'] = (new DataHora())->dataHoraInsert();
                        $simulado['data_fim'] = (new DataHora())->dataHoraInsert();
                        $simulado['ativo'] = $simulado['ativo'] == 'true';

                        if (isset($simulado['novo'])) {
                            $vagas_aberta->Simulados()->create($simulado);
                        } else {
                            $vagas_aberta->Simulados->find($simulado['id'])->update($simulado);
                        }
                    }
                }
                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\VagasAbertas $vagas_aberta
     * @return \Illuminate\Http\Response
     */
    public function show(VagasAbertas $vagas_aberta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\VagasAbertas $vagas_aberta
     * @return VagasAbertas|\Illuminate\Http\Response
     */
    public function edit(VagasAbertas $vagas_aberta)
    {
        $vagas_aberta->load('Municipio', 'Vaga', 'Simulados');

        $vagas_aberta->Simulados->transform(function ($item) {
            $item->tipo_prova = $item->simulado->tipo_prova;
            return $item;
        });

        return $vagas_aberta;

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\VagasAbertas $vagas_aberta
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, VagasAbertas $vagas_aberta)
    {
        $this->authorize('vagas_abertas_update');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dadosValidados = \Validator::make($dados, [
            'vaga_id' => 'required',
            'municipio_id' => 'required'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Vaga',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                $vagas_aberta->update($dados);
                if (isset($dados['simuladosDelete'])) {
                    foreach ($dados['simuladosDelete'] as $id) {
                        $vagas_aberta->Simulados->find($id)->delete();
                    }
                }

                if (isset($dados['simulados'])) {
                    foreach ($dados['simulados'] as $simulado) {

                        if ($simulado['simulado']['tipo_prova'] == 'subjetiva') {
                            $simulado['online'] = false;
                            $simulado['duracao'] = 0;
                        } else {
                            $simulado['online'] = $simulado['online'] == 'true';
                        }

                        $simulado['data_inicio'] = (new DataHora())->dataHoraInsert();
                        $simulado['data_fim'] = (new DataHora())->dataHoraInsert();
                        $simulado['ativo'] = $simulado['ativo'] == 'true';

                        if (isset($simulado['novo'])) {
                            $vagas_aberta->Simulados()->create($simulado);
                        } else {
                            $vagas_aberta->Simulados->find($simulado['id'])->update($simulado);
                        }
                    }
                }
                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\VagasAbertas $vagas_aberta
     * @return \Illuminate\Http\Response
     */
    public function destroy(VagasAbertas $vagas_aberta)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $this->authorize('vagas_abertas');
        $resultado = VagasAbertas::with('Vaga', 'Municipio', 'Simulados.Simulado');
        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Vaga', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%');
            })->orWhere('id', $request->campoBusca);
        }
        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == 'true' ? true : false;
            $resultado->whereAtivo($status);
        }

        $resultado = $resultado->orderByDesc('updated_at')->paginate(50);
        $simulados = Simulado::whereAtivo(true)->orderBy('titulo')->get();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['itens' => $resultado->items(), 'simulados' => $simulados]
        ]);
    }

    public function ativaDesativa(VagasAbertas $vagas_aberta)
    {
        $this->authorize('vagas_abertas_update');
        $vagas_aberta->ativo = !$vagas_aberta->ativo;
        $vagas_aberta->save();
        $vagas_aberta->refresh();
        return response()->json(['ativo' => $vagas_aberta->ativo], 201);
    }

    public function vagaAbertaSimulado($simulado,$vaga_aberta)
    {

        $vaga = VagasAbertas::find($vaga_aberta)->load('Vaga');

        $prova = Simulado::find($simulado)->load('Perguntas');

        $pdf = \PDF::loadView('pdf.cadastro.prova.provasubjetiva', compact('prova','vaga'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("prova.pdf");
    }
}
