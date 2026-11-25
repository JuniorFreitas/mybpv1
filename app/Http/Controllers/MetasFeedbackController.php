<?php

namespace App\Http\Controllers;

use App\Http\Requests\Feedback\StoreMetasFeedbackRequest;
use App\Models\MetasFeedback;
use DB;
use Illuminate\Http\Request;

class MetasFeedbackController extends Controller
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
    public function store(StoreMetasFeedbackRequest $request)
    {
        $this->authorize('admissao_historico');
        $dados = $request->all();

        try {
            DB::beginTransaction();

            MetasFeedback::create($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE META FEEDBACK:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\MetasFeedback $metasFeedback
     * @return \Illuminate\Http\Response
     */
    public function show(MetasFeedback $metasFeedback)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\MetasFeedback $metasFeedback
     * @return \Illuminate\Http\Response
     */
    public function edit(MetasFeedback $metasFeedback)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\MetasFeedback $metasFeedback
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MetasFeedback $metasFeedback)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\MetasFeedback $metasFeedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(MetasFeedback $metasFeedback)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $this->authorize('admissao_historico');
        $metas = MetasFeedback::with('Feedback')->get();

        return response()->json([
            'metas' => $metas,
        ], 200);

    }

}
