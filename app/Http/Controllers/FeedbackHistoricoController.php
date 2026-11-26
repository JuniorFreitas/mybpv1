<?php

namespace App\Http\Controllers;

use App\Http\Requests\Feedback\StoreFeedbackHistoricoRequest;
use App\Models\FeedbackHistorico;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class FeedbackHistoricoController extends Controller
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
    public function store(StoreFeedbackHistoricoRequest $request)
    {
        $this->authorize('admissao_historico');
        $dados = $request->all();

        try {
            DB::beginTransaction();

            FeedbackHistorico::create($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE FEEDBACK HISTORICO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\FeedbackHistorico $feedbackHistorico
     * @return \Illuminate\Http\Response
     */
    public function show(FeedbackHistorico $feedbackHistorico)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\FeedbackHistorico $feedbackHistorico
     * @return \Illuminate\Http\Response
     */
    public function edit(FeedbackHistorico $feedbackHistorico)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\FeedbackHistorico $feedbackHistorico
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FeedbackHistorico $feedbackHistorico)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\FeedbackHistorico $feedbackHistorico
     * @return \Illuminate\Http\Response
     */
    public function destroy(FeedbackHistorico $feedbackHistorico)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $this->authorize('admissao_historico');
        $feedback_historico = FeedbackHistorico::whereFeedbackId($request->feedback_id)->with('Feedback')->get();

        return response()->json([
            'feedback_historico' => $feedback_historico,
            'hoje' => (new DataHora())->dataCompleta()
        ], 200);

    }

}
