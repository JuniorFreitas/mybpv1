<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\JobExportaExcel;
use App\Models\ClienteConfig;
use App\Models\FeriasPrevista;
use App\Models\User;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class FeriasController extends Controller
{
    public function index()
    {
        return view('g.relatorios.ferias.index');
    }

    public function show(Request $request)
    {
        $periodo = explode(' até ', $request->periodo);
        $dataInicio = new DataHora($periodo[0], ' 00:00:00');
        $dataFim = new DataHora($periodo[1], ' 23:59:59');

        $empresa_id = auth()->user()->empresa_id;
        $periodo_vencimento = ClienteConfig::LISTA_VENCIMENTOS[auth()->user()->EmpresaConfiguracoes->verifica_mes_vencimento];
        $periodo_vencimento = preg_replace("/[^0-9]/", "", $periodo_vencimento);

        $feriasPrevistas = FeriasPrevista::select([
            'id', 'colaborador_id', 'centro_custo_id', 'data_saida', 'qnt_dias', 'data_retorno', 'dias_saldo', 'status_aprovacao',
            'tem_faltas', 'qnt_faltas', 'periodo_aquisitivo_id', 'periodo_aquisitivo', 'ultima_data', 'user_aprovacao_id', 'data_aprovacao', 'obs_aprovacao', 'gestor_id'
        ])->whereEmpresaId($empresa_id)
            ->whereNull('status_aprovacao')
            ->where('ultima_data', '>=', $dataInicio->dataInsert())
            ->where('ultima_data', '<=', $dataFim->dataInsert())
            ->whereHas('Feedback', function ($q) {
                $q->Admitidos();
            })->with(['Feedback' => function ($F) {
                $F->Admitidos()->select(['id', 'curriculo_id', 'empresa_id', 'vaga_id'])
                    ->with('Admissao:id,feedback_id,data_admissao')
                    ->with('VagaSelecionada:id,nome')
                    ->with('Curriculo', function ($C) {
                        $C->select(['id', 'nome', 'nascimento', 'rg', 'orgao_expeditor']);
                    });
            }, 'CentroCusto:id,label', 'QuemAprovou:id,nome', 'GestorAprovacao:id,nome']);

        $feriasPrevistas = $feriasPrevistas->get();

        $resultado = collect();

        foreach ($feriasPrevistas as $ferias) {
            $dias_vencer = DataHora::diferencaDias((new DataHora())->dataInsert(), $ferias->ultima_data);
            $resultado->push([
                'nome' => $ferias->Feedback->Curriculo->nome,
                'cargo' => $ferias->Feedback->VagaSelecionada->nome,
                'data_admissao' => $ferias->Feedback->Admissao->data_admissao,
                'gestor' => $ferias->GestorAprovacao->nome,
                'quem_aprovou' => $ferias->QuemAprovou ? $ferias->QuemAprovou->nome : "Sistema",
                'centro_custo' => $ferias->CentroCusto->label,
                'data_saida' => $ferias->data_saida,
                'data_retorno' => $ferias->data_retorno,
                'qnt_dias' => $ferias->qnt_dias,
                'dias_saldo' => $ferias->dias_saldo,
                'tem_faltas' => $ferias->tem_faltas ? "Sim" : "Não",
                'qnt_faltas' => (int)$ferias->qnt_faltas,
                'periodo_aquisitivo' => $ferias->periodo_aquisitivo,
                'ultima_data' => $ferias->ultima_data,
                'status_aprovacao' => $ferias->status_aprovacao,
                'dias_vencer' => $dias_vencer,
                'pintar' => $dias_vencer <= $periodo_vencimento / 2,
            ]);
        }

        return $resultado->sortBy('dias_vencer')->values()->all();

    }

    public function exportExcel(Request $request)
    {
        $ferias = $this->show($request);

        $head = [
            'nome',
            'cargo',
            'data_admissao',
            'gestor',
            'quem_aprovou',
            'centro_custo',
            'data_saida',
            'data_retorno',
            'qnt_dias',
            'dias_saldo',
            'tem_faltas',
            'qnt_faltas',
            'periodo_aquisitivo',
            'ultima_data',
            'status_aprovacao',
            'dias_vencer',
        ];
        $rows = [];

        foreach ($ferias as $row) {
            $rows[] = array(
                $row['nome'],
                $row['cargo'],
                $row['data_admissao'],
                $row['gestor'],
                $row['quem_aprovou'],
                $row['centro_custo'],
                $row['data_saida'],
                $row['data_retorno'],
                $row['qnt_dias'],
                $row['dias_saldo'],
                $row['tem_faltas'],
                $row['qnt_faltas'],
                $row['periodo_aquisitivo'],
                $row['ultima_data'],
                $row['status_aprovacao'],
                $row['dias_vencer'],
            );
        }

        $nameArquivo = "vencimento_ferias_" . \Str::slug('Vencimento Ferias') . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Vencimento Ferias ", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }
}
