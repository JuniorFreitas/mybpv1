<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\Treinamento;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class TreinamentoController extends Controller
{
    public function index()
    {
        return view('g.relatorios.treinamento.index');
    }

    public function show(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;

        $periodo = explode(' até ', $request->periodo);
        $dataInicio = new DataHora($periodo[0], ' 00:00:00');
        $dataFim = new DataHora($periodo[1], ' 23:59:59');

        $treinamentos = Treinamento::select(['id', 'feedback_id', 'tipo'])
            ->whereHas('FeedbackCurriculo', function ($q) use ($empresa_id) {
                $q->Admitidos()->whereEmpresaId($empresa_id);
            })->whereHas('Vencimentos', function ($q) use ($dataInicio,$dataFim) {
                $q->where('treinamento_vencimento.data_vencimento', '>=', $dataInicio->dataInsert())
                    ->where('treinamento_vencimento.data_vencimento', '<=', $dataFim->dataInsert());
            })->with(['Vencimentos' => function ($q) use ($dataInicio,$dataFim) {
                $q->where('treinamento_vencimento.data_vencimento', '>=', $dataInicio->dataInsert())
                    ->where('treinamento_vencimento.data_vencimento', '<=', $dataFim->dataInsert());
            }, 'FeedbackCurriculo:id,empresa_id,vaga_id,curriculo_id',
                'FeedbackCurriculo.VagaSelecionada:id,nome',
                'FeedbackCurriculo.Curriculo:id,nome,nascimento,rg,orgao_expeditor'])
            ->get();

        $resultado = collect();

        foreach ($treinamentos as $treinamento) {

            $vencimentos = collect();
            $treinamento->Vencimentos->each(function ($model) use ($vencimentos) {
                $vencimentos->push([
                    'label' => $model->label,
                    'descricao' => $model->descricao,
                    'data_vencimento' => $model->pivot->data_vencimento,
                    'data_treinamento' => $model->pivot->data_treinamento,
                    'dias_vencer' => DataHora::diferencaDias((new DataHora())->dataInsert(), $model->pivot->data_vencimento)
                ]);
            });

            $resultado->push([
                'nome' => $treinamento->FeedbackCurriculo->Curriculo->nome,
                'cargo' => $treinamento->FeedbackCurriculo->VagaSelecionada->nome,
                'tipo' => $treinamento->tipo,
                'treinamentos' => $vencimentos,
            ]);
        }

        return response()->json($resultado);
    }
}
