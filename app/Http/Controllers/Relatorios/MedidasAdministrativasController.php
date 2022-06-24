<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\JobExportaExcel;
use App\Models\MedidaAdministrativa;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class MedidasAdministrativasController extends Controller
{
    public function index()
    {
        return view('g.relatorios.medidasadministrativas.index');
    }

    public function show(Request $request){
        $empresa_id = auth()->user()->empresa_id;
        
        $MedidasAdm = MedidaAdministrativa::whereHas('Feedback', function($q) use ($empresa_id) {
            $q->where('empresa_id', $empresa_id);
        })->with('Feedback', function ($F) {
            $F->select(['id', 'curriculo_id', 'empresa_id', 'vaga_id'])
                ->Admitidos()
                ->with('VagaSelecionada:id,nome')
                ->with('Curriculo', function ($C) {
                    $C->select(['id', 'nome', 'nascimento', 'rg', 'orgao_expeditor']);
                });
        });

        $periodo = explode(' até ', $request->periodo);
        $dataInicio = new DataHora($periodo[0], ' 00:00:00');
        $dataFim = new DataHora($periodo[1], ' 23:59:59');
        
        $MedidasAdm->whereHas('Feedback', function ($q) use ($dataInicio, $dataFim) {
            $q->where('data_solicitacao', '>=', $dataInicio->dataInsert())->where('data_solicitacao', '<=', $dataFim->dataInsert());
        });

        $MedidasAdm = $MedidasAdm->get();

        $medidas = collect();
        foreach ($MedidasAdm as $medida) {
            $medidas->push([
                'nome' => $medida->Feedback->Curriculo->nome,
                'cargo' => $medida->Feedback->VagaSelecionada->nome,
                'motivo' => $medida->motivo,
                'causa' => $medida->causa,
                'data_solicitacao' => $medida->data_solicitacao,
                'data_retorno' => $medida->data_retorno,
                'solicitante' => $medida->solicitante,
                'tipo' => $medida->tipo
            ]);
        }

        return $medidas;
    
    }
    
    public function exportExcel(Request $request)
    {
        $medidas = $this->show($request);
        
        $head = [
            'nome',
            'cargo',
            'motivo',
            'causa',
            'data_solicitacao',
            'data_retorno',
            'solicitante',
            'tipo'
        ];
        $rows = [];

        foreach ($medidas as $row) {
            $rows[] = array(
                $row['nome'],
                $row['cargo'],
                $row['motivo'],
                $row['causa'],
                $row['data_solicitacao'],
                $row['data_retorno'],
                $row['solicitante'],
                $row['tipo']
            );
        }

        $nameArquivo = "medidas_administrativas_" . \Str::slug('Medidas Administrativas') . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Medidas Administrativas ", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

}
