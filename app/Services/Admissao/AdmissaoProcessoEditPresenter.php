<?php

namespace App\Services\Admissao;

use App\Models\FeedbackCurriculo;

/**
 * Monta o payload JSON de edição do processo de Admissão (TD-002 closeout).
 */
class AdmissaoProcessoEditPresenter
{
    /**
     * @return array{feedback: FeedbackCurriculo}
     */
    public function montar(FeedbackCurriculo $feedback): array
    {
        $feedback->load(
            'Admissao.DadosAdmissoes',
            'Admissao.FeriasAdquiridas',
            'Admissao.UltimoAso',
            'UltimoAso',
            'Curriculo.Escolaridade',
            'Curriculo.Dependentes',
            'Curriculo.FotoTres',
            'Curriculo.Telefones',
            'Curriculo.Dependentes',
            'parecerRh',
            'parecerTecnica',
            'parecerRota',
            'parecerTeste',
            'VagaSelecionada',
            'Cliente:id,razao_social,cnpj,nome,cpf,area_id',
            'Cliente.Area',
            'Cliente.AreasEtiquetas',
            'TelPrincipal',
            'BancoConta',
            'ResultadoIntegrado',
        );

        if (!is_null($feedback->BancoConta)) {
            $feedback->BancoConta->banco = $feedback->BancoConta->banco ?: 'Banco do Brasil';
            $feedback->BancoConta->agencia = $feedback->BancoConta->agencia ?: '';
            $feedback->BancoConta->conta = $feedback->BancoConta->conta ?: '';
            $feedback->BancoConta->pix = $feedback->BancoConta->pix ?: false;
            $feedback->BancoConta->tipochavepix = $feedback->BancoConta->tipochavepix ?: '';
            $feedback->BancoConta->chavepix = $feedback->BancoConta->chavepix ?: '';
        }

        if ($feedback->Curriculo) {
            $feedback->Curriculo->foto_tres_delete = [];
            $feedback->Curriculo->dependentesDelete = [];
            $feedback->Curriculo->telefonesDelete = [];
        }

        if (!is_null($feedback->Admissao)) {
            $centroResultado = (int) ($feedback->ResultadoIntegrado?->centro_custo_id ?? 0);
            if (empty($feedback->Admissao->centro_custo_id) && $centroResultado > 0) {
                $feedback->Admissao->centro_custo_id = $centroResultado;
            }
            $feedback->Admissao->ferias_adquiridasDelete = [];
            $feedback->Admissao->documento = $feedback->Admissao->documento ?: '';
            $feedback->Admissao->documento_portaria = $feedback->Admissao->documento_portaria ?: '';
            $feedback->Admissao->tipo_admissao = $feedback->Admissao->tipo_admissao ?: '';
            $feedback->Admissao->treinamento = $feedback->Admissao->treinamento ?: '';
            $feedback->Admissao->nr_trinta_tres = $feedback->Admissao->nr_trinta_tres ?: '';
            $feedback->Admissao->nr_trinta_cinco = $feedback->Admissao->nr_trinta_cinco ?: '';
            $feedback->Admissao->status_carteira_treinamento = $feedback->Admissao->status_carteira_treinamento ?: '';
            $feedback->Admissao->area_etiqueta_id = $feedback->Admissao->area_etiqueta_id == 0 ? null : $feedback->Admissao->area_etiqueta_id;
            $feedback->Admissao->documento = $feedback->Admissao->documento ?? '';
            $feedback->Admissao->documento_portaria = $feedback->Admissao->documento_portaria ?? '';
            $feedback->Admissao->tipo_admissao = $feedback->Admissao->tipo_admissao ?? '';
            $feedback->Admissao->tipo_treinamento = $feedback->Admissao->tipo_treinamento ?? '';
            $feedback->Admissao->treinamento = $feedback->Admissao->treinamento ?? '';
            $feedback->Admissao->nr_trinta_tres = $feedback->Admissao->nr_trinta_tres ?? '';
            $feedback->Admissao->nr_trinta_cinco = $feedback->Admissao->nr_trinta_cinco ?? '';
            $feedback->Admissao->trinta_dois_sessenta = $feedback->Admissao->trinta_dois_sessenta ?? '';
            $feedback->Admissao->foto_escaneada = $feedback->Admissao->foto_escaneada ?? '';
            $feedback->Admissao->status = $feedback->Admissao->status ?? '';
            $feedback->Admissao->data_admissao = $feedback->Admissao->data_admissao ?? '';
            $feedback->Admissao->matricula = $feedback->Admissao->matricula ?? '';
            $feedback->Admissao->salario = $feedback->Admissao->salario ?? '0,00';
            $feedback->Admissao->prazo_experiencia = $feedback->Admissao->prazo_experiencia ?? '';
        }

        if ($feedback->parecerRh) {
            $feedback->parecerRh->indicado_por = $feedback->parecerRh->indicado_por ?: '';
            $feedback->parecerRh->calca = $feedback->parecerRh->calca ?: '';
            $feedback->parecerRh->bota = $feedback->parecerRh->bota ?: '';
            $feedback->parecerRh->camisa_protecao = $feedback->parecerRh->camisa_protecao ?: '';
            $feedback->parecerRh->camisa_meia = $feedback->parecerRh->camisa_meia ?: '';
        }

        if (!is_null($feedback->parecerTecnica)) {
            $feedback->parecerTecnica->indicado_area = $feedback->parecerTecnica->indicado_area ?: '';
        }

        $labelVaga = '';
        if ($feedback->relationLoaded('VagaAberta') || $feedback->VagaAberta) {
            $vagaAberta = $feedback->VagaAberta;
            if ($vagaAberta && $vagaAberta->VagaSelecionada && $vagaAberta->Municipio) {
                $labelVaga = $vagaAberta->VagaSelecionada->nome
                    . ' - ' . $vagaAberta->Municipio->nome
                    . ' - ' . $vagaAberta->Municipio->uf;
            }
        }
        $feedback->autocomplete_label_vaga_modal = $labelVaga;
        $feedback->autocomplete_label_vaga_modal_anterior = $labelVaga;

        return ['feedback' => $feedback];
    }
}
