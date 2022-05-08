<?php

namespace App\Http\Controllers;

use App\Jobs\JobExportaExcel;
use App\Models\Admissao;
use App\Models\Curriculo;
use App\Models\EntrevistaRh;
use App\Models\FeedbackCurriculo;
use App\Models\GestorRh;
use App\Models\IndividualRh;
use App\Models\ParecerEntrevistaTecnica;
use App\Models\ParecerRh;
use App\Models\ParecerRota;
use App\Models\ParecerTestePratico;
use App\Models\Projeto;
use App\Models\ResultadoIntegrado;
use App\Models\Treinamento;
use App\Models\VagaProjeto;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MasterTag\DataHora;
use PDF;

class MobilizacaoController extends Controller
{
    public function index()
    {
        return view('g.planejamento.mobilizacao.index');
    }

    public function getprojetos()
    {
        $projetos = Projeto::whereEmpresaId(auth()->user()->empresa_id)->with('VagasProjeto.VagaAberta.VagaSelecionada')->get()->map(function ($projeto) {
            $projeto->text = $projeto->nome;
            return $projeto;
        });
        return response()->json($projetos);
    }

    /**
     * @param Collection | array $feedbacks
     * @param array | string $status
     * @return int
     */
    private function statusAdmissao($feedbacks, $status)
    {
        is_array($status) ?: $status = [$status];
        return Admissao::whereIn('feedback_id', $feedbacks)->whereIn('status', $status)->count();
    }

    /**
     * @param Collection | array $feedbacks
     * @param string $tipo
     * @return int
     */
    private function tipoAdmissao($feedbacks, $tipo)
    {
        return Admissao::whereIn('feedback_id', $feedbacks)->where('tipo_admissao', $tipo)->count();
    }

    /**
     * @param $projeto
     * @return \Illuminate\Http\JsonResponse|Collection
     */
    public function selecionaProjeto($projeto): Collection
    {
        $projeto = Projeto::whereId($projeto)->whereEmpresaId(auth()->user()->empresa_id)->first();

        if (!$projeto) {
            return response()->json(['msg' => 'Sem autorização'], 403);
        }

        $vagasProjeto = VagaProjeto::whereEmpresaId(auth()->user()->empresa_id)
            ->whereProjetoId($projeto->id)
            ->with('VagaAberta:id,vaga_id,empresa_id,titulo', 'VagaAberta.Vaga:id,empresa_id,nome')
            ->get();

        $vagasProjeto = $vagasProjeto->transform(function ($item) {
            $feedbacks = FeedbackCurriculo::whereVagaProjetoId($item->id)->pluck('id');

            $item->feedbacks = $feedbacks->count();

            //Admissao status
            $item->em_processo_selecao = $this->statusAdmissao($feedbacks, Admissao::STATUS_EM_PROCESSO_SELECAO);
            $item->status_aguardando_qualificacao = $this->statusAdmissao($feedbacks, Admissao::STATUS_ADMISSAO_AGUARDANDOQUALIFICACAO);
            $item->status_pronto_para_admissao = $this->statusAdmissao($feedbacks, Admissao::STATUS_ADMISSAO_PRONTOPARAADMISSAO);
            $item->status_admitido = $this->statusAdmissao($feedbacks, Admissao::STATUS_ADMISSAO_ADMITIDO);
            $item->status_standby = $this->statusAdmissao($feedbacks, Admissao::STATUS_ADMISSAO_STANDBY);
            $item->status_pendente_aso = $this->statusAdmissao($feedbacks, Admissao::STATUS_ADMISSAO_PENDENTEASO);
            $item->status_aso_no_ambulatorio = $this->statusAdmissao($feedbacks, Admissao::STATUS_ADMISSAO_ASO_NO_AMBULATORIO);
            $item->status_pendente_documento = $this->statusAdmissao($feedbacks, Admissao::STATUS_ADMISSAO_PENDENTEDOCUMENTO);
            $item->status_pendente_treinamento = $this->statusAdmissao($feedbacks, Admissao::STATUS_ADMISSAO_PENDENTETREINAMENTO);
            $item->status_cancelado = $this->statusAdmissao($feedbacks, Admissao::STATUS_ADMISSAO_CANCELADO);
            $item->status_encaminhado_exame = $this->statusAdmissao($feedbacks, Admissao::STATUS_ADMISSAO_ENCAMINHADOEXAME);
            $item->status_desistencia = $this->statusAdmissao($feedbacks, Admissao::STATUS_ADMISSAO_DESISTENCIA);
            $item->treinamento_fase_1 = Treinamento::whereIn('feedback_id', $feedbacks)->whereHas('Vencimentos', function ($q) {
                $q->whereIn('label', ['NR33', 'NR35']);
            })->count();

            $item->treinados = Treinamento::whereIn('feedback_id', $feedbacks)->count();
            $item->documento_portaria = Admissao::whereIn('feedback_id', $feedbacks)->where('documento_portaria', Admissao::DOC_CONCLUIDO)->count();
            $item->entregue_area = Admissao::whereIn('feedback_id', $feedbacks)->whereNotNull('data_entrega_area')->count();

            //Tipos de admissão
            $item->tipo_admissao_temporario = $this->tipoAdmissao($feedbacks, Admissao::TIPO_ADMISSAO_TEMPORARIO);
            $item->tipo_admissao_intermitente = $this->tipoAdmissao($feedbacks, Admissao::TIPO_ADMISSAO_INTERMITENTE);
            $item->tipo_admissao_determinado = $this->tipoAdmissao($feedbacks, Admissao::TIPO_ADMISSAO_DETERMINADO);
            $item->tipo_admissao_fixo = $this->tipoAdmissao($feedbacks, Admissao::TIPO_ADMISSAO_FIXO);

            return $item;
        });

        $feedbackSelecionado = FeedbackCurriculo::where('selecionado', 'sim')->whereInteresse(true);
        $feedbackStandBy = FeedbackCurriculo::where('selecionado', 'standby')->whereInteresse(true);

        return collect([
            'projeto' => $projeto,
            'vagas_projeto' => $vagasProjeto,
            'total_geral_curriculos' => Curriculo::count(),
            'total_geral_curriculos_feedbacks' => FeedbackCurriculo::count(),
            'total_geral_curriculos_selecionados' => $feedbackSelecionado->count(),
            'total_em_parecer_rh' => ParecerRh::whereIn('feedback_id', $feedbackSelecionado->pluck('id'))->count(),
            'total_em_parecer_rota' => ParecerRota::whereIn('feedback_id', $feedbackSelecionado->pluck('id'))->count(),
            'total_em_parecer_teste' => ParecerTestePratico::whereIn('feedback_id', $feedbackSelecionado->pluck('id'))->count(),
            'total_em_parecer_tecnica' => ParecerEntrevistaTecnica::whereIn('feedback_id', $feedbackSelecionado->pluck('id'))->count(),
            'total_em_individual_rh' => IndividualRh::whereIn('feedback_id', $feedbackSelecionado->pluck('id'))->count(),
            'total_em_gestor_rh' => GestorRh::whereIn('feedback_id', $feedbackSelecionado->pluck('id'))->count(),
            'total_em_entrevista_rh' => EntrevistaRh::whereIn('feedback_id', $feedbackSelecionado->pluck('id'))->count(),
            'total_em_resultado_integrado' => ResultadoIntegrado::whereIn('feedback_id', $feedbackSelecionado->pluck('id'))->count(),
            'total_geral_curriculos_standby' => FeedbackCurriculo::where('selecionado', 'standby')->whereInteresse(true)->count(),
            'total_geral_curriculos_homens' => Curriculo::whereSexo('Masculino')->count(),
            'total_geral_curriculos_homens_selecionados' => $feedbackSelecionado->whereHas('Curriculo', function ($q) {
                $q->whereSexo('Masculino');
            })->count(),
            'total_geral_curriculos_homens_standby' => $feedbackStandBy->whereHas('Curriculo', function ($q) {
                $q->whereSexo('Masculino');
            })->count(),
            'total_geral_curriculos_mulheres' => Curriculo::whereSexo('Feminino')->count(),
            'total_geral_curriculos_mulheres_selecionadas' => FeedbackCurriculo::where('selecionado', 'sim')->whereInteresse(true)->whereHas('Curriculo', function ($q) {
                $q->whereSexo('Feminino');
            })->count(),
            'total_geral_curriculos_mulheres_standby' => FeedbackCurriculo::where('selecionado', 'standby')->whereInteresse(true)->whereHas('Curriculo', function ($q) {
                $q->whereSexo('Feminino');
            })->count(),
            'total_geral_treinados' => Treinamento::whereIn('feedback_id', $feedbackSelecionado->pluck('id'))->count(),
            'total_admitidos_treinados' => Admissao::whereStatus(Admissao::STATUS_ADMISSAO_ADMITIDO)->whereIn('feedback_id', $feedbackSelecionado->pluck('id'))->count(),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $resultado = (object)$this->selecionaProjeto($request->projeto);
        $head = [
            'Projeto',
            'Quantidade total no projeto',
            'Quantidade preenchidas no projeto',
            'Vaga Aberta / Cargo',
            'Total',
            'Preenchidas',
            'Em Processo de Seleção',
            'Treinamento Fase 1',
            'Pendente Treinamento',
            'Exames',
            'Portaria',
            'Aguardando Qualificação',
            'ASO no Ambulatório',
            'Cancelados',
            'Desistências',
            'Encaminhado para Exames',
            'Pendente ASO',
            'Pendente Documentos',
            'Pronto para admissão',
            'Admitidos',
            'Entregues na área',
            'StandBy',
            'Admissão tipo determinada',
            'Admissão tipo fixo',
            'Admissão tipo intermitente',
            'Admissão tipo temporária',
        ];
        $rows = [];

        foreach ($resultado['vagas_projeto'] as $row) {
            $rows[] = array(
                $resultado['projeto']->nome,
                $resultado['projeto']->qnt_total == 0 ? "0" : $resultado['projeto']->qnt_total,
                $resultado['projeto']->preenchidas == 0 ? "0" : $resultado['projeto']->preenchidas,
                $row->VagaAberta->titulo . ' - (' . $row->VagaAberta->Vaga->nome . ')',
                $row->qnt_total == 0 ? "0" : $row->qnt_total,
                $row->qnt_preenchida == 0 ? "0" : $row->qnt_preenchida,
                $row->em_processo_selecao == 0 ? "0" : $row->em_processo_selecao,
                $row->treinamento_fase_1 == 0 ? "0" : $row->treinamento_fase_1,
                $row->status_pendente_treinamento == 0 ? "0" : $row->status_pendente_treinamento,
                $row->status_encaminhado_exame == 0 ? "0" : $row->status_encaminhado_exame,
                $row->documento_portaria == 0 ? "0" : $row->documento_portaria,
                $row->status_aguardando_qualificacao == 0 ? "0" : $row->status_aguardando_qualificacao,
                $row->status_aso_no_ambulatorio == 0 ? "0" : $row->status_aso_no_ambulatorio,
                $row->status_cancelado == 0 ? "0" : $row->status_cancelado,
                $row->status_desistencia == 0 ? "0" : $row->status_desistencia,
                $row->status_encaminhado_exame == 0 ? "0" : $row->status_encaminhado_exame,
                $row->status_pendente_aso == 0 ? "0" : $row->status_pendente_aso,
                $row->status_pendente_documento == 0 ? "0" : $row->status_pendente_documento,
                $row->status_pronto_para_admissao == 0 ? "0" : $row->status_pronto_para_admissao,
                $row->status_admitido == 0 ? "0" : $row->status_admitido,
                $row->entregue_area == 0 ? "0" : $row->entregue_area,
                $row->status_standby == 0 ? "0" : $row->status_standby,
                $row->tipo_admissao_determinado == 0 ? "0" : $row->tipo_admissao_determinado,
                $row->tipo_admissao_fixo == 0 ? "0" : $row->tipo_admissao_fixo,
                $row->tipo_admissao_intermitente == 0 ? "0" : $row->tipo_admissao_intermitente,
                $row->tipo_admissao_temporario == 0 ? "0" : $row->tipo_admissao_temporario
            );
        }

        $nameArquivo = "mobilizacao_projeto_" . \Str::slug($resultado['projeto']->nome) . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Mobilização - Projeto " . $resultado['projeto']->nome, $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

    public function geraPdf($projeto)
    {
        $dados = (object)$this->selecionaProjeto($projeto);
        $pdf = PDF::loadView('pdf.planejamento.mobilizacao.projeto', compact('dados'));
        $pdf->setPaper('A4');

        return $pdf->stream("mobilizacao_projeto_" . \Str::slug($dados['projeto']->nome) . (new DataHora())->nomeUnico() . ".pdf");
    }
}
