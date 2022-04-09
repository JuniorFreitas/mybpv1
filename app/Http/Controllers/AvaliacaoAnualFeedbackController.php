<?php

namespace App\Http\Controllers;

use App\Models\AvaliacaoAnualFeedback;
use App\Models\AvaliacaoAnualFeedbackQuantidade;
use App\Models\Topicos;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;
use PDF;

class AvaliacaoAnualFeedbackController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'gestor_imediato' => 'required'
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar as Notas',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $qntAvaliacao = AvaliacaoAnualFeedbackQuantidade::whereFeedbackId($dados['feedback_id'])->count();

                $qntAvaliacao += 1;

                $info = [
                    'gestor_id' => auth()->user()->id,
                    'gestor_imediato' => $dados['gestor_imediato'],
                    'observacao' => $dados['observacao'],
                    'feedback_id' => $dados['feedback_id'],
                    'quantidade_avaliacao' => $qntAvaliacao,
                ];
                AvaliacaoAnualFeedbackQuantidade::create($info);

                foreach ($dados['topicos'] as $topico) {
                    foreach ($topico['perguntas'] as $form) {
                        $formulario = [];
                        $formulario['feedback_id'] = $dados['feedback_id'];
                        $formulario['pergunta_id'] = $form['id'];
                        $formulario['nota'] = $form['nota'];
                        $formulario['quantidade_avaliacao'] = $qntAvaliacao;
                        AvaliacaoAnualFeedback::create($formulario);
                    }
                }
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE AVALIACAO NOVENTA FEEDBACK:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
                return response()->json(['msg' => $msg], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function show($id)
    {
        $topicos = Topicos::with('Perguntas')->get()->transform(function ($item) {
            $item->Perguntas->transform(function ($tem) {
                $tem->nota = '';
                return $tem;
            });
            return $item;
        });

        $tabela = AvaliacaoAnualFeedbackQuantidade::whereFeedbackId($id)->get();

        return response()->json([
            'topicos' => $topicos,
            'tabela' => $tabela,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function avaliacaoAnualPDF($quantidade_avaliacao, $feedback_id)
    {
        $informacoes = AvaliacaoAnualFeedbackQuantidade::whereFeedbackId($feedback_id)->whereQuantidadeAvaliacao($quantidade_avaliacao)->first();
        $avaliacao = AvaliacaoAnualFeedback::whereFeedbackId($feedback_id)->whereQuantidadeAvaliacao($quantidade_avaliacao)->get();

        $pdf = PDF::loadView('pdf.admissao.historico.avaliacaoanual.avaliacao', compact('avaliacao', 'informacoes'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream((new DataHora())->nomeUnico() . ".pdf");
    }
}
