<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\Relatorios\VencimentoAso\JobExportarExcel;
use App\Models\ClienteConfig;
use App\Models\ExameTipo;
use App\Models\FeedbackCurriculo;
use App\Models\User;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class VencimentoAsosController extends Controller
{

    public function index()
    {
        return view('g.relatorios.vencimentoasos.index');
    }

    public static function filtro($empresa_id, $dados)
    {
        $Empresa = User::find($empresa_id);

        $periodo_vencimento = ClienteConfig::LISTA_VENCIMENTOS[$Empresa->EmpresaConfiguracoes->vencimento_aso];
        $periodo_vencimento = (int)preg_replace("/[^0-9]/", "", $periodo_vencimento);

        $data = new DataHora();
        $data->addDia($periodo_vencimento);

        $examesFuncionarios = FeedbackCurriculo::select(['id', 'curriculo_id', 'empresa_id', 'vaga_id', 'vagas_abertas_id'])
            ->admitidos()
            ->whereHas('UltimoAso', function ($q) use ($dados) {
                $filtroVencimento = $dados['filtroVencimento'] == 'true';
                if ($filtroVencimento) {
                    $periodo = explode(' até ', $dados['campoVencimento']);
                    $dataInicio = new DataHora($periodo[0]);
                    $dataFim = new DataHora($periodo[1]);
                    $q->where('data_vencimento', '>=', $dataInicio->dataInsert() . ' 00:00:00')->where('data_vencimento', '<=', $dataFim->dataInsert() . ' 23:59:59');
                }
                if (!is_null($dados['campoVencido'])) {
                    $q->where('vencido', $dados['campoVencido']);
                }
            })
            ->whereHas('UltimoAso.ExameFuncionario', function ($q) use ($dados) {
                if (!is_null($dados['campoTipoExame'])) {
                    $q->where('exame_tipo_id', $dados['campoTipoExame']);
                }
            })
            ->with('UltimoAso.ExameFuncionario:id,feedback_id,exame_tipo_id',
                'UltimoAso.ExameFuncionario.ExameTipo:id,label')
            ->with('Curriculo:id,nome,nascimento,rg,orgao_expeditor')
            ->whereHas('Curriculo', function ($q) use ($dados) {
                if (!is_null($dados['campoBusca'])) {
                    $q->where('nome', 'like', '%' . $dados['campoBusca'] . '%');
                }
            })
            ->with('Admissao:id,feedback_id,cargo,data_admissao')
            ->with('VagaAberta:id,vaga_id,titulo,municipio_id,empresa_id')
            ->groupBy('id')->get();

        $examesFuncionarios = $examesFuncionarios->map(function ($item) {
            return [
                'feedback_id' => $item->id,
                'colaborador' => $item->Curriculo->nome,
                'cargo' => $item->Admissao ? $item->Admissao->cargo : $item->VagaAberta->VagaSelecionada->nome,
                'data_admissao' => $item->Admissao->data_admissao ?? 'Não informada',
                'exame_tipo' => $item->UltimoAso->ExameFuncionario[0]->ExameTipo->label,
                'data_aso' => $item->UltimoAso->data_realizacao,
                'data_vencimento' => $item->UltimoAso->data_vencimento,
                'dias_vencer' => DataHora::diferencaDias((new DataHora())->dataInsert() . ' 00:00:00', (new DataHora($item->UltimoAso->data_vencimento))->dataInsert() . ' 23:59:59'),
            ];
        });

        return $examesFuncionarios->sortBy('dias_vencer')->values()->all();
    }

    public function show(Request $request)
    {
        $examesFuncionarios = self::filtro(auth()->user()->empresa_id, $request->input());
        return response()->json($examesFuncionarios);
    }

    public function exportExcel(Request $request)
    {
        $nomeArquivo = "_vencimento_asos_" . auth()->id() . '_' . rand(1000, 9999) . "_" . date('YmdHis');
        JobExportarExcel::dispatch(auth()->id(), "VencimentoAso", $request->input(), $nomeArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }


    public function tiposExames()
    {
        return ExameTipo::whereAtivo(true)->get();
    }
}
