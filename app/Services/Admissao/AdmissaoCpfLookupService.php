<?php

namespace App\Services\Admissao;

use App\Models\Admissao;
use App\Models\Curriculo;
use App\Models\EntrevistaDesligamento;
use App\Models\Sistema;
use MasterTag\DataHora;

/**
 * Consulta um CPF para decidir se já existe admissão em aberto, ou monta os dados do
 * currículo encontrado para pré-carregar o formulário (inclusive caso de recontratação).
 * Extraído de AdmissaoController::buscaCPF (TD-002 fatia 2).
 */
class AdmissaoCpfLookupService
{
    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function lookup(string $cpfBruto): array
    {
        $cpf = Sistema::transformCpfCnpj($cpfBruto);

        $admissao = Admissao::whereHas('Feedback.Curriculo', function ($q) use ($cpf) {
            $q->whereCpf($cpf);
        });

        $demissao = Admissao::whereHas('Demissao')->whereHas('Feedback.Curriculo', function ($q) use ($cpf) {
            $q->whereCpf($cpf);
        });

        // Se o candidato já possui cadastro de admissão ativo (sem ser um ex-funcionário)
        if ($admissao->count() > 0 && $demissao->count() == 0) {
            return [
                'status' => 400,
                'body' => [
                    'msg' => "Candidato {$admissao->first()->Feedback->Curriculo->id} - {$admissao->first()->Feedback->Curriculo->nome} ja possui cadastro de admissão desde " . DataHora::dataFormatada($admissao->first()->created_at),
                ],
            ];
        }

        $curriculo = Curriculo::whereCpf($cpf);

        if ($curriculo->count() === 0) {
            return [
                'status' => 200,
                'body' => ['achou' => false, 'ex_funcionario' => false],
            ];
        }

        return [
            'status' => 200,
            'body' => $this->montarDadosCurriculoEncontrado($curriculo->first(), $admissao, $demissao),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function montarDadosCurriculoEncontrado($curriculo, $admissao, $demissao): array
    {
        $curriculo = $curriculo->load('Dependentes', 'FotoTres');
        $curriculo->dependentesDelete = [];
        $curriculo->foto_tres_delete = [];

        $curriculo->pcd = $curriculo->pcd ?: false;

        $curriculo->autocomplete_label_municipio_modal = $curriculo->Cidade ? $curriculo->Cidade->nome . ' - ' . $curriculo->Cidade->uf : '';
        $curriculo->autocomplete_label_municipio_modal_anterior = $curriculo->Cidade ? $curriculo->Cidade->nome . ' - ' . $curriculo->Cidade->uf : '';

        $feedback = new \stdClass();
        $feedback->vaga_id = '';
        $feedback->cliente_id = '';
        $feedback->interesse = true;
        $feedback->autocomplete_label_vaga_modal = '';
        $feedback->autocomplete_label_vaga_modal_anterior = '';
        $feedback->autocomplete_label_cliente_modal = '';
        $feedback->autocomplete_label_cliente_modal_anterior = '';

        $parecerRH = new \stdClass();
        $parecerRH->ex_funcionario = $admissao->count() > 0;
        $parecerRH->calca = '';
        $parecerRH->bota = '';
        $parecerRH->camisa_protecao = '';
        $parecerRH->camisa_meia = '';
        $parecerRH->turnos_seis_por_dois = '';
        $parecerRH->indicacao = '';
        $parecerRH->indicado_por = '';

        $parecerTecnica = new \stdClass();
        $parecerTecnica->experiencia_cargas_rigger = 'NÃO SE APLICA';
        $parecerTecnica->opera_plat_movel = 'NÃO SE APLICA';
        $parecerTecnica->opera_plat_ponte = 'NÃO SE APLICA';
        $parecerTecnica->indicado_area = '';

        $parecerRota = new \stdClass();
        $parecerRota->bairro_rota = '';
        $parecerRota->ponto_referencia_rota = '';
        $parecerRota->ponto_referencia_residencia = '';

        $parecerTeste = new \stdClass();
        $parecerTeste->qual_teste = '';
        $parecerTeste->parecer_final_teste = '';

        $resultadoIntegrado = new \stdClass();
        $resultadoIntegrado->documentos_entregue = '';
        $resultadoIntegrado->documentos_entregue_data = '';
        $resultadoIntegrado->encaminhado_exame = '';
        $resultadoIntegrado->encaminhado_exame_data = '';
        $resultadoIntegrado->encaminhado_treinamento = '';
        $resultadoIntegrado->encaminhado_treinamento_data = '';
        $resultadoIntegrado->excessao = '';
        $resultadoIntegrado->autorizado_por = '';
        $resultadoIntegrado->responsavel_envio = '';
        $resultadoIntegrado->obs = $demissao->count() > 0 ? 'RECONTRATAÇÃO' : 'ADMISSÃO AVULSA';

        $admissaoDemitido = $demissao->with('DadosAdmissoes')->first();

        if ($admissaoDemitido && $admissaoDemitido->DadosAdmissoes) {
            $dadosAdmissao = new \stdClass();
            $dadosAdmissao->pis = $admissaoDemitido->pis;
            $dadosAdmissao->dados_admissoes = $admissaoDemitido->DadosAdmissoes;
        } else {
            $dadosAdmissao = new \stdClass();
            $dadosAdmissao->pis = '';
            $dadosAdmissao->dados_admissoes = (object) [
                'ctps_numero' => '',
                'ctps_serie' => '',
                'ctps_uf' => '',
                'ctps_data_emissao' => '',
                'titulo_eleitor_numero' => '',
                'titulo_eleitor_sessao' => '',
                'titulo_eleitor_zona' => '',
                'cert_reservista_num' => '',
                'cert_reservista_categoria' => '',
            ];
        }

        if ($curriculo->FeedBack && $curriculo->FeedBack->BancoConta) {
            $feedback->banco_conta = $curriculo->FeedBack->BancoConta;
        } else {
            // Sempre enviar banco_conta: Object.assign no front substitui feedback
            // e, sem esta chave, o select "Tem PIX?" fica sem model válido.
            $feedback->banco_conta = (object) [
                'banco' => 'Banco do Brasil',
                'agencia' => '',
                'conta' => '',
                'pix' => false,
                'tipochavepix' => '',
                'chavepix' => '',
            ];
        }

        // Pode voltar (recontratação)
        $podeVoltar = true;
        if ($curriculo->FeedBack) {
            $entrevistaDesligamento = EntrevistaDesligamento::select(['id', 'feedback_id', 'pode_voltar'])
                ->where('feedback_id', $curriculo->FeedBack->id)
                ->first();
            if ($demissao->count() > 0 && $entrevistaDesligamento) {
                $podeVoltar = !$entrevistaDesligamento->pode_voltar; // Se for false, não pode voltar
            }
        }

        return [
            'achou' => true,
            'admissao' => $dadosAdmissao,
            'ex_funcionario' => $demissao->count() > 0,
            'pos_admissao_verificar' => $podeVoltar,
            'curriculo' => $curriculo->load('Telefones'),
            'feedback' => $feedback,
            'parecer_rh' => $parecerRH,
            'parecer_tecnica' => $parecerTecnica,
            'parecer_rota' => $parecerRota,
            'parecer_teste' => $parecerTeste,
            'resultado_integrado' => $resultadoIntegrado,
        ];
    }
}
