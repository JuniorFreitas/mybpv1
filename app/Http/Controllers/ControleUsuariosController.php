<?php

namespace App\Http\Controllers;

use App\Models\Admissao;
use App\Models\Curriculo;
use App\Models\FeedbackCurriculo;
use App\Models\ParecerEntrevistaTecnica;
use App\Models\ParecerRh;
use App\Models\ParecerRota;
use App\Models\ParecerTestePratico;
use App\Models\ResultadoIntegrado;
use App\Models\Treinamento;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class ControleUsuariosController extends Controller
{
    public function index()
    {
        return view('g.relatorios.controleusuarios.index');
    }

    public function dadosusuarioSistema(Request $request)
    {

        $dataInicio = (new DataHora($request->data_inicio . ' 00:00:00'))->dataHoraInsert();
        $dataFim = (new DataHora($request->data_fim . ' 23:59:59'))->dataHoraInsert();

        if ($request->tipo == 'curriculos_abertos') {
            $curriculosAbertos = Curriculo::whereUsuarioLido($request->usuario_id)
                ->where('lido', true)->where('datalido', '>=', $dataInicio)
                ->where('datalido', '<=', $dataFim);

            $conta = $curriculosAbertos->count();

            return response()->json([
                'data' => $curriculosAbertos->orderBy('updated_at')->get(),
                'tipo' => 'curriculos_abertos',
                'usuario_nome' => $request->autocomplete_label_usuario_modal,
                'total' => $conta
            ], 200);
        }

        if ($request->tipo == 'curriculos_feedback') {
            $feedback = FeedbackCurriculo::whereUsuarioEntrevistaMarcado($request->usuario_id)
                ->where('updated_at', '>=', $dataInicio)
                ->where('updated_at', '<=', $dataFim)
                ->with('Curriculo');

            $conta = $feedback->count();

            return response()->json([
                'data' => $feedback->orderBy('updated_at')->get(),
                'tipo' => 'curriculos_feedback',
                'usuario_nome' => $request->autocomplete_label_usuario_modal,
                'total' => $conta
            ], 200);
        }

        if ($request->tipo == 'admissao') {
            $admissao = Admissao::whereEditadoUsuarioId($request->usuario_id)
                ->where('updated_at', '>=', $dataInicio)
                ->where('updated_at', '<=', $dataFim)
                ->with('Feedback.Curriculo');

            $conta = $admissao->count();

            return response()->json([
                'data' => $admissao->orderBy('updated_at')->get(),
                'tipo' => 'admissao',
                'usuario_nome' => $request->autocomplete_label_usuario_modal,
                'total' => $conta
            ], 200);
        }

        if ($request->tipo == 'parecer_rh') {
            $parecerRh = ParecerRh::whereEntrevistador($request->usuario_id)
                ->where('created_at', '>=', $dataInicio)
                ->where('created_at', '<=', $dataFim)
                ->with('FeedbackCurriculo.Curriculo');

            $conta = $parecerRh->count();
            $resultado = $parecerRh->orderBy('created_at')->get();
            return response()->json([
                'data' => $resultado,
                'tipo' => 'parecer_rh',
                'usuario_nome' => $request->autocomplete_label_usuario_modal,
                'total' => $conta
            ], 200);
        }

        if ($request->tipo == 'parecer_rota') {
            $parecerRota = ParecerRota::whereAprovadoPor($request->usuario_id)
                ->where('created_at', '>=', $dataInicio)
                ->where('created_at', '<=', $dataFim)
                ->with('FeedbackCurriculo.Curriculo');

            $conta = $parecerRota->count();
            $resultado = $parecerRota->orderBy('created_at')->get();
            return response()->json([
                'data' => $resultado,
                'tipo' => 'parecer_rota',
                'usuario_nome' => $request->autocomplete_label_usuario_modal,
                'total' => $conta
            ], 200);
        }

        if ($request->tipo == 'parecer_tecnica') {
            $parecerTecnica = ParecerEntrevistaTecnica::whereEntrevistadoPor($request->usuario_id)
                ->where('created_at', '>=', $dataInicio)
                ->where('created_at', '<=', $dataFim)
                ->with('FeedbackCurriculo.Curriculo');

            $conta = $parecerTecnica->count();
            $resultado = $parecerTecnica->orderBy('created_at')->get();
            return response()->json([
                'data' => $resultado,
                'tipo' => 'parecer_tecnica',
                'usuario_nome' => $request->autocomplete_label_usuario_modal,
                'total' => $conta
            ], 200);

        }

        if ($request->tipo == 'parecer_teste') {
            $parecerTeste = ParecerTestePratico::whereEntrevistador($request->usuario_id)
                ->where('created_at', '>=', $dataInicio)
                ->where('created_at', '<=', $dataFim)
                ->with('FeedbackCurriculo.Curriculo');

            $conta = $parecerTeste->count();
            $resultado = $parecerTeste->orderBy('created_at')->get();
            return response()->json([
                'data' => $resultado,
                'tipo' => 'parecer_teste',
                'usuario_nome' => $request->autocomplete_label_usuario_modal,
                'total' => $conta
            ], 200);
        }

        if ($request->tipo == 'resultado_integrado') {
            $resultadoIntegrado = ResultadoIntegrado::whereUsuarioId($request->usuario_id)
                ->where('created_at', '>=', $dataInicio)
                ->where('created_at', '<=', $dataFim)
                ->with('Feedback.Curriculo');

            $conta = $resultadoIntegrado->count();
            $resultado = $resultadoIntegrado->orderBy('created_at')->get();
            return response()->json([
                'data' => $resultado,
                'tipo' => 'resultado_integrado',
                'usuario_nome' => $request->autocomplete_label_usuario_modal,
                'total' => $conta
            ], 200);
        }

        if ($request->tipo == 'treinamentos') {
            $treinamentos = Treinamento::whereGerouId($request->usuario_id)
                ->where('created_at', '>=', $dataInicio)
                ->where('created_at', '<=', $dataFim)
                ->with('FeedbackCurriculo.Curriculo');

            $conta = $treinamentos->count();
            $resultado = $treinamentos->orderBy('created_at')->get();
            return response()->json([
                'data' => $resultado,
                'tipo' => 'treinamentos',
                'usuario_nome' => $request->autocomplete_label_usuario_modal,
                'total' => $conta
            ], 200);
        }

    }
}
