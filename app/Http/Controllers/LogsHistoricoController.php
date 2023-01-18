<?php

namespace App\Http\Controllers;

use App\Models\FeedbackHistorico;
use App\Models\LogHistorico;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class LogsHistoricoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\LogHistorico $logHistorico
     * @return \Illuminate\Http\Response
     */
    public function show(LogHistorico $logHistorico)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\LogHistorico $logHistorico
     * @return \Illuminate\Http\Response
     */
    public function edit(LogHistorico $logHistorico)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\LogHistorico $logHistorico
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LogHistorico $logHistorico)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\LogHistorico $logHistorico
     * @return \Illuminate\Http\Response
     */
    public function destroy(LogHistorico $logHistorico)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $this->authorize('admissao_historico');
        $resultado = LogHistorico::whereFeedbackId($request->feedback_id)
                                 ->with('Usuario:id,nome')
                                 ->orderBy('id', 'desc')
                                 ->paginate(50);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items()
            ]
        ]);
    }

}
