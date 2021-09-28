<?php

namespace App\Http\Controllers;

use App\Models\PromocaoFeedback;
use DB;
use Illuminate\Http\Request;

class PromocaoFeedbackController extends Controller
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

        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'novo_cargo' => 'required',
            'novo_salario' => 'required',
            'motivo' => 'required',
            'percentual' => 'required',
            'tipo' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar a Promoção',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                PromocaoFeedback::create($dados);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE PROMOÇÃO FEEDBACK:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
                return response()->json(['msg' => $msg], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\PromocaoFeedback $promocaoFeedback
     * @return \Illuminate\Http\Response
     */
    public function show(PromocaoFeedback $promocaoFeedback)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\PromocaoFeedback $promocaoFeedback
     * @return PromocaoFeedback|\Illuminate\Http\Response
     */
    public function edit(PromocaoFeedback $promocaoFeedback)
    {
        return $promocaoFeedback;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\PromocaoFeedback $promocaoFeedback
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PromocaoFeedback $promocaoFeedback)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\PromocaoFeedback $promocaoFeedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(PromocaoFeedback $promocaoFeedback)
    {
        //
    }

    public function atualizar($feedback)
    {
        $promocoes = PromocaoFeedback::where('feedback_id', $feedback)->get();

        return response()->json([
            'promocoes' => $promocoes
        ], 200);
    }
}
