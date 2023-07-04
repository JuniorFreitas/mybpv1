<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\Admissao;
use App\Models\AdmissaoAso;
use App\Models\AlternativaFormulario;
use App\Models\ClienteConfig;
use App\Models\ExameFuncionario;
use App\Models\Examesesmt;
use App\Models\ExameTipo;
use App\Models\FeedbackCurriculo;
use App\Models\RespostaAlternativas;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class VencimentoAsosController extends Controller
{

    public function index()
    {
        return view('g.relatorios.vencimentoasos.index');
    }

    public function show(Request $request)
    {
        $dados = $request->input();
        $periodo_vencimento = ClienteConfig::LISTA_VENCIMENTOS[auth()->user()->EmpresaConfiguracoes->vencimento_aso];
        $empresa_id = auth()->user()->empresa_id;

        $periodo_vencimento = preg_replace("/[^0-9]/", "", $periodo_vencimento);
        $data = new DataHora();
        $data->addDia($periodo_vencimento);

        $examesFuncionarios = FeedbackCurriculo::select(['id', 'curriculo_id', 'empresa_id', 'vaga_id', 'vagas_abertas_id'])
            ->whereHas('UltimoAso', function ($q) use ($request){
                $filtroVencimento = $request->filtroVencimento == 'true' ? true : false;
                if ($filtroVencimento) {
                    $periodo = explode(' até ', $request->campoVencimento);
                    $dataInicio = new DataHora($periodo[0]);
                    $dataFim = new DataHora($periodo[1]);
                    $q->where('data_vencimento', '>=', $dataInicio->dataInsert())->where('data_vencimento', '<=', $dataFim->dataInsert());
                }
                if(!is_null($request->campoVencido)){
                    $q->where('vencido', $request->campoVencido);
                }
            })
            ->whereHas('UltimoAso.ExameFuncionario', function ($q) use ($request) {
                if (!is_null($request->campoTipoExame)) {
                    $q->where('exame_tipo_id', $request->campoTipoExame);
                }
            })
            ->with('UltimoAso.ExameFuncionario:id,feedback_id,exame_tipo_id','UltimoAso.ExameFuncionario.ExameTipo:id,label')
            ->with('Curriculo:id,nome,nascimento,rg,orgao_expeditor')
            ->whereHas('Curriculo', function ($q) use ($dados) {
                if(!is_null($dados['campoBusca'])) {
                    $q->where('nome', 'like', '%' . $dados['campoBusca'] . '%');
                }
            })
            ->with('Admissao:id,feedback_id,cargo,data_admissao')
            ->with('VagaAberta:id,vaga_id,titulo,municipio_id,empresa_id')
            ->with('VagaAberta.VagaSelecionada:id,nome','VagaAberta.Municipio')
            ->groupBy('id')->get();

        $examesFuncionarios = $examesFuncionarios->map(function ($item){
            return [
                'feedback_id' => $item->id,
                'colaborador' => $item->Curriculo->nome,
                'cargo' => $item->VagaAberta->VagaSelecionada->nome,
                'data_admissao' => $item->Admissao->data_admissao ?? 'Não informada',
                'exame_tipo' => $item->UltimoAso->ExameFuncionario[0]->ExameTipo->label,
                'data_aso' => $item->UltimoAso->data_realizacao,
                'data_vencimento' => $item->UltimoAso->data_vencimento,
                'dias_vencer' => DataHora::diferencaDias((new DataHora())->dataInsert(), (new DataHora($item->UltimoAso->data_vencimento))->dataInsert())
            ];
        });
        return response()->json($examesFuncionarios);
    }

    public function tiposExames()
    {
       return ExameTipo::whereAtivo(true)->get();
    }
}
