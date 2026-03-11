<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\Relatorios\VencimentoAso\JobExportarExcel;
use App\Models\Admissao;
use App\Models\CentroCusto;
use App\Models\ClienteConfig;
use App\Models\Examesesmt;
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

    public static function filtro($empresa_id, $dados, $request)
    {
        $Empresa = User::find($empresa_id);
        $config = $Empresa?->EmpresaConfiguracoes;
        $vencimentoAso = $config->vencimento_aso ?? null;
        if (!$config || !isset(ClienteConfig::LISTA_VENCIMENTOS[$vencimentoAso])) {
            return [];
        }
        $periodo_vencimento = (int) preg_replace("/[^0-9]/", "", ClienteConfig::LISTA_VENCIMENTOS[$vencimentoAso]);
        $data = (new DataHora())->addDia($periodo_vencimento);
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj($empresa_id);

        $examesFuncionarios = FeedbackCurriculo::select([
            'id', 'curriculo_id', 'empresa_id', 'vaga_id', 'vagas_abertas_id'
        ])
            ->admitidos()->whereHas('Admissao', function ($query) use ($data) {
                $query->whereIn('status', [
                    Admissao::STATUS_ADMISSAO_ADMITIDO,
                    Admissao::STATUS_ADMISSAO_PRONTOPARAADMISSAO,
                ]);
            })
            ->filtrarPorCnpjECentroCusto($request)
            ->filtrarPorUltimoAso($dados)
            ->filtrarPorTipoExame($dados)
            ->with([
                'UltimoAso.ExameFuncionario:id,feedback_id,exame_tipo_id',
                'UltimoAso.ExameFuncionario.ExameTipo:id,label',
                'Admissao:id,feedback_id,data_admissao,matricula,funcao,numero_cracha,status,cargo,centro_custo_filial_id,centro_custo_id,filial',
                'Curriculo:id,nome,nascimento,rg,orgao_expeditor',
                'VagaAberta:id,vaga_id,titulo,municipio_id,empresa_id'
            ])
            ->filtrarPorNome($dados)
            ->groupBy('id')
            ->get();

        $examesFuncionarios = $examesFuncionarios->map(function ($item) use ($cc, $periodo_vencimento) {
            $item = (new self())->prepararAdmissao($item, $cc);
            $ultimoAso = $item->UltimoAso;
            $exameFuncionario = $ultimoAso->ExameFuncionario->first();
            $exameTipoLabel = $exameFuncionario?->ExameTipo?->label ?? '—';
            $cargo = $item->Admissao?->cargo ?? $item->VagaAberta?->VagaSelecionada?->nome ?? '—';
            return [
                'emp_cnpj' => $item->Admissao?->emp_cnpj ?? null,
                'emp_nome_fantasia' => $item->Admissao?->emp_nome_fantasia ?? null,
                'emp_centro_custo' => $item->Admissao?->emp_centro_custo ?? null,
                'emp_tipo' => $item->Admissao?->emp_tipo ?? null,
                'feedback_id' => $item->id,
                'atual' => $ultimoAso->atual,
                'colaborador' => $item->Curriculo?->nome ?? '—',
                'cargo' => $cargo,
                'data_admissao' => $item->Admissao?->data_admissao ?? 'Não informada',
                'exame_tipo' => $exameTipoLabel,
                'data_aso' => $ultimoAso->data_realizacao,
                'data_vencimento' => $ultimoAso->data_vencimento,
                'dias_vencer' => DataHora::diferencaDias((new DataHora())->dataInsert() . ' 00:00:00', (new DataHora($ultimoAso->data_vencimento))->dataInsert() . ' 23:59:59'),
            ];
        });

        return $examesFuncionarios->sortBy('dias_vencer')->values()->all();
    }

    private function prepararAdmissao($item, $cc)
    {
        if ($item->Admissao && ! empty($cc['centros_custos'])) {
            $cc_colaborador = collect($cc['centros_custos'])->collapse()->where('id', $item->Admissao->centro_custo_id)->first();
            $item->Admissao->emp_cnpj = null;
            $item->Admissao->emp_nome_fantasia = null;
            $item->Admissao->emp_centro_custo = null;
            $item->Admissao->emp_tipo = null;

            if ($cc_colaborador) {
                $item->Admissao->emp_cnpj = $cc_colaborador['cnpj_format'];
                $item->Admissao->emp_nome_fantasia = $cc_colaborador['nome_fantasia'];
                $item->Admissao->emp_centro_custo = $cc_colaborador['label'];
                $item->Admissao->emp_tipo = $cc_colaborador['matriz'] ? 'Matriz' : 'Filial';
            }
        }
        return $item;
    }

    public function show(Request $request)
    {
        $user = auth()->user();
        $config = $user->EmpresaConfiguracoes;
        if (!$config || !isset(ClienteConfig::LISTA_VENCIMENTOS[$config->vencimento_aso ?? null])) {
            return response()->json([
                'dados' => [],
                'periodo_vencimento_numero' => 90,
                'periodo_vencimento_extenso' => '90 dias',
                'cc' => (new CentroCusto())->listaCentroCustoPorCnpj($user->empresa_id),
            ]);
        }

        $empresa_id = $user->empresa_id;
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj($empresa_id);
        $examesFuncionarios = self::filtro($empresa_id, $request->input(), $request);
        $periodo_vencimento = ClienteConfig::LISTA_VENCIMENTOS[$config->vencimento_aso];
        $periodo_vencimento_num = (int) preg_replace("/[^0-9]/", "", $periodo_vencimento);

        return response()->json([
            'dados' => $examesFuncionarios,
            'periodo_vencimento_numero' => $periodo_vencimento_num,
            'periodo_vencimento_extenso' => $periodo_vencimento,
            'cc' => $cc,
        ]);
    }


    public function tiposExames()
    {
        return ExameTipo::whereAtivo(true)->get();
    }
}
