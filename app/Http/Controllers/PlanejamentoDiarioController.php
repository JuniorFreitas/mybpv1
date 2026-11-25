<?php

namespace App\Http\Controllers;

use App\Http\Requests\Operacional\StorePlanejamentoDiarioRequest;
use App\Http\Requests\Operacional\UpdatePlanejamentoDiarioRequest;
use App\Models\AtaReuniao;
use App\Models\PlanejamentoDiario;
use App\Models\PlanejamentoDiarioTarefas;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;
use PDF;

class PlanejamentoDiarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        return view('g.administracao.planejamentodiario.index');
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
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(StorePlanejamentoDiarioRequest $request)
    {
        $this->authorize('administracao_planejamentodiario');
        $dados = $request->input();
        $dados['user_id'] = auth()->user()->id;

        try {
            DB::beginTransaction();

            $id = PlanejamentoDiario::create($dados)->id;

            foreach ($dados['tarefas'] as $t) {
                $ta = [
                    'planejamento_id' => $id,
                    'tarefa' => $t['tarefa'],
                    'status' => $t['status'],
                ];
                PlanejamentoDiarioTarefas::create($ta);
            }

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE PLANEJAMENTO DIARIO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} - Usuario ".auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
        return PlanejamentoDiario::where('id', $id)->where('user_id', auth()->user()->id)->with('Tarefas')->first();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(UpdatePlanejamentoDiarioRequest $request, $id)
    {
        $this->authorize('administracao_planejamentodiario');
        $dados = $request->input();

        try {
            DB::beginTransaction();

            foreach ($dados['tarefas'] as $t) {
                $pdt = PlanejamentoDiarioTarefas::where('id', $t['id'])->where('planejamento_id', $id)->first();
                $pdt->update(['status' => $t['status']]);
            }

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE PLANEJAMENTO DIARIO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}";
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
            //return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
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

    public function atualizar(Request $request)
    {
        $this->authorize('administracao_planejamentodiario');
        $porPagina = $request->get('porPagina');
        $resultado = PlanejamentoDiario::where('user_id', auth()->user()->id);

        // se tiver busca
        if ($request->filled('campoBusca')) {
            $resultado->where(function ($q) use ($request) {
                $q->where('assunto', 'like', '%' . $request->campoBusca . '%')
                    ->orWhereHas('Respostas', function ($q) use ($request) {
                        $q->where('resposta', 'like', '%' . $request->campoBusca . '%');
                    });
            });
        }
        // se for um tipo Problema ou Anotação
        if ($request->filled('campoTipo')) {
            $resultado->where('tipo', $request->campoTipo);
        }

        $resultado = $resultado->orderByDesc('updated_at')->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
            ]
        ], 200);

    }

    public function pdf($item)
    {

        $atareuniao = AtaReuniao::where('id', $item)->with('Assuntos', 'Tipos', 'Acoes', 'Participantes', 'QuemCadastrou')->first();

        //dd($atareuniao);

        $pdf = PDF::loadView('pdf.administracao.atareuniao.atareuniao', compact('atareuniao'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('ata_de_reuniao_' . (new DataHora())->nomeUnico() . ".pdf");
    }
}
