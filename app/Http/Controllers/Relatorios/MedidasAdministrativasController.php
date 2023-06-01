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

    public function show(Request $request)
    {
        $periodo = explode(' até ', $request->periodo);
        $dataInicio = new DataHora($periodo[0], ' 00:00:00');
        $dataFim = new DataHora($periodo[1], ' 23:59:59');

        $medidas = MedidaAdministrativa::whereHas('Feedback', function ($query) use ($request) {
            if ($request->filled('status')) {
                if ($request->status == 'admitidos') {
                    $query->admitidos();
                }
                if ($request->status == 'demitidos') {
                    $query->demitidos();
                }
            }
        })->with(
            'Feedback:id,curriculo_id,empresa_id,vaga_id,vagas_abertas_id',
            'Feedback.Curriculo:id,nome,rg,orgao_expeditor,nascimento',
            'Feedback.VagaAberta:id,vaga_id,titulo'
        )->where('data_solicitacao', '>=', $dataInicio->dataInsert())
            ->where('data_solicitacao', '<=', $dataFim->dataInsert())
            ->get()->map(function ($medida) {
                return [
                    'nome' => $medida->Feedback->Curriculo->nome,
                    'cargo' => $medida->Feedback->VagaAberta->Vaga->nome,
                    'motivo' => $medida->motivo,
                    'causa' => $medida->causa,
                    'data_solicitacao' => $medida->data_solicitacao,
                    'data_retorno' => $medida->data_retorno,
                    'solicitante' => $medida->solicitante,
                    'tipo' => $medida->tipo
                ];
            });

        return $medidas;

    }

    public function exportExcel(Request $request)
    {
        $medidas = $this->show($request);


        $head = [
            'Nome',
            'Cargo',
            'Motivo',
            'Causa',
            'Data Solicitação',
            'Data Retorno',
            'Solicitante',
            'Tipo'
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
