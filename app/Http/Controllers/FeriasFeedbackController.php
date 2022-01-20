<?php

namespace App\Http\Controllers;

use App\Models\AfastamentoFeedback;
use App\Models\FeriasFeedback;
use App\Models\FeriasPrevista;
use App\Models\FeriasPrevistaDados;
use App\Models\FeriasPrevistaMov;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;
use PDF;

class FeriasFeedbackController extends Controller
{

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
            'ano' => 'required',
            'valor' => 'required'
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar as Férias',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $dados['quem_cadastrou'] = auth()->id();

                FeriasFeedback::create($dados);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE FERIAS FEEDBACK:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
                return response()->json(['msg' => $msg], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\FeriasFeedback $feriasFeedback
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function show($curriculo_id)
    {

        $ferias = FeriasPrevista::whereColaboradorId($curriculo_id)->with(
            'Colaborador',
            'CentroCusto',
            'UserCadastrou',
            'QuemAprovou',
            'PeriodoAquisitivo',
        );

        return response()->json([
            'ferias' => $ferias->get(),
            'hoje' => (new DataHora())->dataCompleta()
        ], 200);
    }

    public function feriasPDF($id, $feedback_id)
    {
        $ferias = FeriasFeedback::with('Usuario', 'Feedback')->whereId($id)->whereFeedbackId($feedback_id)->first();
        $pdf = PDF::loadView('pdf.admissao.historico.ferias.ficha', compact('ferias'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream((new DataHora())->nomeUnico() . ".pdf");

    }
}
