<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\Admissao;
use App\Models\AdmissaoAso;
use App\Models\ClienteConfig;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class VencimentoAsosController extends Controller
{

    public function index()
    {
        return view('g.relatorios.vencimentoasos.index');
    }

    public function show()
    {
        $periodo_vencimento = ClienteConfig::LISTA_VENCIMENTOS[auth()->user()->EmpresaConfiguracoes->vencimento_aso];
        $empresa_id = auth()->user()->empresa_id;

        $periodo_vencimento = preg_replace("/[^0-9]/", "", $periodo_vencimento);
        $data = new DataHora();
        $data->addDia($periodo_vencimento);

        $AdmissoesAso = AdmissaoAso::whereAtivo(true)->whereEmpresaId($empresa_id)
            ->whereHas('Admissao', function ($q) {
                $q->Admitidos();
            })
            ->select(['id', 'empresa_id', 'admissao_id', 'data_aso', 'data_vencimento'])
//            ->where('data_vencimento', '>=', (new DataHora())->dataInsert())
            ->where('data_vencimento', '<=', $data->dataInsert())
            ->where('ativo',true)
            ->with('Admissao', function ($a) {
                $a->select(['id', 'feedback_id', 'data_admissao','data_aso'])
                    ->Admitidos()
                    ->with('Feedback', function ($F) {
                        $F->select(['id', 'curriculo_id', 'empresa_id', 'vaga_id'])
                            ->with('VagaSelecionada:id,nome')
                            ->with('Curriculo', function ($C) {
                                $C->select(['id', 'nome', 'nascimento', 'rg', 'orgao_expeditor']);
                            });
                    });
            })
            ->orderBy('data_vencimento')
            ->get()
            ->toArray();

        $vencimentos = collect();
        foreach ($AdmissoesAso as $vencimento) {
            if ($vencimentos->where('admissao_id', $vencimento['admissao_id'])->count() == 0) {
                $vencimentos->push([
                    'admissao_id' => $vencimento['admissao_id'],
                    'colaborador' => $vencimento['admissao']['feedback']['curriculo']['nome'],
                    'cargo' => $vencimento['admissao']['feedback']['vaga_selecionada']['nome'],
                    'data_admissao' => $vencimento['admissao']['data_admissao'],
                    'data_aso' => $vencimento['data_aso'],
                    'data_aso_adm' => (new DataHora($vencimento['admissao']['data_aso']))->dataCompleta(),
                    'data_vencimento' => $vencimento['data_vencimento_formatada'],
                    'dias_vencer' => DataHora::diferencaDias((new DataHora())->dataInsert(), $vencimento['data_vencimento'])
                ]);
            }
        }

        return response()->json($vencimentos);
    }
}
