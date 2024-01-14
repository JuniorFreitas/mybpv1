<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\JobExportaExcel;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\FeedbackCurriculo;
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
        $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
        $dataFim = new DataHora($periodo[1] . ' 23:59:59');


        $colaboradores = FeedbackCurriculo::select([
            'id', 'curriculo_id', 'telefone_id', 'vaga_id', 'vagas_abertas_id', 'vaga_projeto_id'
        ])->Admitidos()->whereHas('ResultadoIntegrado', function ($q) {
            $q->whereEncaminhadoTreinamento(true);
        })->whereEmpresaId($empresa_id)->with(
            'Curriculo:id,nome,cpf,nascimento,pcd,uf_vaga,email,rg,orgao_expeditor',
            'Admissao:id,feedback_id,area_etiqueta_id,data_admissao,matricula,funcao,nr_trinta_cinco,nr_trinta_tres,numero_cracha,status,cargo,centro_custo_filial_id,centro_custo_id,filial',
            'Treinamento:id,cadastrou,feedback_id,tipo,created_at,updated_at',
            'Treinamento.Vencimentos',
            'Treinamento.QuemCadastrou:id,nome'
        )->filtrarPorCnpjECentroCusto($request);

        $colaboradores = $colaboradores->whereHas('Treinamento.Vencimentos', function ($q) use ($dataInicio, $dataFim) {
            $q->where('treinamento_vencimento.data_vencimento', '>=', $dataInicio->dataHoraInsert())
                ->where('treinamento_vencimento.data_vencimento', '<=', $dataFim->dataHoraInsert());
        })->orderByDesc('created_at')->get();

        $cc = (new CentroCusto())->listaCentroCustoPorCnpj($empresa_id);

        $colaboradores->transform(function ($model) use ($cc) {
            if ($model->Admissao) {
                $cc_colaborador = collect($cc['centros_custos'])->collapse()->where('id', $model->Admissao->centro_custo_id)->first();
                $model->Admissao->emp_cnpj = null;
                $model->Admissao->emp_nome_fantasia = null;
                $model->Admissao->emp_centro_custo = null;
                $model->Admissao->emp_tipo = null;

                if ($cc_colaborador) {
                    $model->Admissao->emp_cnpj = $cc_colaborador['cnpj_format'];
                    $model->Admissao->emp_nome_fantasia = $cc_colaborador['nome_fantasia'];
                    $model->Admissao->emp_centro_custo = $cc_colaborador['label'];
                    $model->Admissao->emp_tipo = $cc_colaborador['matriz'] ? 'Matriz' : 'Filial';
                }
            }
            return $model;
        });


        $resultado = collect();


        foreach ($colaboradores as $colaborador) {
            $vencimentos = collect();
            $colaborador->treinamento->Vencimentos->each(function ($model) use ($vencimentos) {
                $dias_vencer = DataHora::diferencaDias((new DataHora())->dataInsert(), $model->pivot->data_vencimento);
                $vencimentos->push([
                    'label' => $model->label,
                    'descricao' => $model->descricao,
                    'data_vencimento' => $model->pivot->data_vencimento,
                    'data_treinamento' => $model->pivot->data_treinamento,
                    'dias_vencer' => $dias_vencer,
                    'pintar' => $dias_vencer <= 30
                ]);
            });

            $resultado->push([
                'nome' => $colaborador->Curriculo->nome,
                'cargo' => $colaborador->VagaAberta->Vaga->nome ?? 'NÃO ENCONTRADO',
                'emp_cnpj' => $colaborador->Admissao->emp_cnpj ?? '--',
                'emp_nome_fantasia' => $colaborador->Admissao->emp_nome_fantasia ?? '--',
                'emp_centro_custo' => $colaborador->Admissao->emp_centro_custo ?? '--',
                'emp_tipo' => $colaborador->Admissao->emp_tipo ?? '--',
                'tipo' => $colaborador->treinamento->tipo,
                'treinamentos' => collect($vencimentos)->sortBy('dias_vencer')->values(),
            ]);
        }


        $resultado = $resultado->transform(function ($item) {
            $tCollect = collect($item['treinamentos']);
            $item['pintar'] = $tCollect->where('pintar', true)->count() == $tCollect->count();
            $item['count_pintar'] = $tCollect->where('pintar', true)->count();
            return $item;
        })->sortByDesc('count_pintar')
            ->sortBy('pintar', SORT_REGULAR, true)->values();


        return response()->json([
            'cc' => $cc,
            'itens' => $resultado,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $treinamentos = $this->show($request);
        $head = [
            'nome',
            'cargo',
            'tipo',
            'treinamento',
            'data_treinamento',
            'data_vencimento',
            'dias_vencer',
        ];
        $rows = [];

        foreach ($treinamentos as $row) {
            foreach ($row['treinamentos'] as $treinamento) {
                $rows[] = [
                    $row['nome'],
                    $row['cargo'],
                    $row['tipo'],
                    $treinamento['label'],
                    $treinamento['data_treinamento'],
                    $treinamento['data_vencimento'],
                    $treinamento['dias_vencer']
                ];
            }
        }

        $nameArquivo = "vencimento_treinamento" . \Str::slug('Vencimento Treinamento') . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Vencimento Treinamento ", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }
}
