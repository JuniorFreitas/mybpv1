<?php

namespace App\Http\Controllers;

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
use App\Models\VagaProjeto;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

    public function selecionaProjeto(Request $request, Projeto $projeto)
    {
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

            //Tipos de admissão
            $item->tipo_admissao_temporario = $this->tipoAdmissao($feedbacks, Admissao::TIPO_ADMISSAO_TEMPORARIO);
            $item->tipo_admissao_intermitente = $this->tipoAdmissao($feedbacks, Admissao::TIPO_ADMISSAO_INTERMITENTE);
            $item->tipo_admissao_determinado = $this->tipoAdmissao($feedbacks, Admissao::TIPO_ADMISSAO_DETERMINADO);
            $item->tipo_admissao_fixo = $this->tipoAdmissao($feedbacks, Admissao::TIPO_ADMISSAO_FIXO);

            return $item;
        });

        $feedbackSelecionado = FeedbackCurriculo::where('selecionado', 'sim')->whereInteresse(true);
        $feedbackStandBy = FeedbackCurriculo::where('selecionado', 'standby')->whereInteresse(true);

        return response()->json([
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

        ]);
    }
}
