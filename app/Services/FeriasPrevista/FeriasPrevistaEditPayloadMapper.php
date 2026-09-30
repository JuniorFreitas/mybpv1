<?php

namespace App\Services\FeriasPrevista;

use App\Models\Arquivo;
use App\Models\Ferias;
use App\Models\User;

/**
 * Monta payload enxuto do modal editar/visualizar/aprovar Férias (movimentação).
 */
class FeriasPrevistaEditPayloadMapper
{
    public const FERIAS_COLUMNS = [
        'id',
        'admissao_id',
        'periodo_aquisitivo_id',
        'data_saida',
        'data_retorno',
        'ultima_data',
        'qnt_dias',
        'dias_saldo',
        'tem_faltas',
        'qnt_faltas',
        'solicitante_id',
        'obs_solicitante',
        'data_solicitacao',
        'gestor_aprovacao_id',
        'gestor_id',
        'obs_gestor',
        'status_aprovacao_gestor',
        'data_aprovacao_gestor',
        'aprovacao_extra_id',
        'status_aprovacao_extra',
        'obs_aprovacao_extra',
        'data_aprovacao_extra',
        'rh_aprovacao_id',
        'obs_rh',
        'status_aprovacao_rh',
        'data_aprovacao_rh',
        'aprovado_via_script',
        'abono_pecuniario',
        'adiantamento_decimo_terceiro',
        'empresa_id',
        'ferias_prevista_id',
    ];

    public const USER_COLUMNS = ['id', 'nome'];

    public const ANEXO_COLUMNS = [
        'arquivos.id',
        'arquivos.nome',
        'arquivos.file',
        'arquivos.disco',
        'arquivos.imagem',
        'arquivos.thumb',
        'arquivos.extensao',
        'arquivos.bytes',
        'arquivos.chave',
        'arquivos.temporario',
    ];

    /**
     * @return array<string, mixed>
     */
    public function map(Ferias $item): array
    {
        $admissao = $item->Admissao;
        $curriculo = $admissao?->Feedback?->Curriculo;
        $colaboradorNome = $curriculo?->nome ?? '';
        $gestorNome = $item->Gestor?->nome ?? '';
        $centroCustoId = $admissao?->centro_custo_id
            ?? $item->FeriasPrevista?->centro_custo_id;

        return [
            'id' => (int) $item->id,
            'admissao_id' => $item->admissao_id,
            'colaborador_id' => $curriculo?->id ?? '',
            'autocomplete_label_colaborador' => $colaboradorNome,
            'autocomplete_label_colaborador_anterior' => $colaboradorNome,
            'data_admissao' => $admissao?->data_admissao ?? '',
            'centro_custo_id' => $centroCustoId,
            'filial' => (bool) ($admissao?->filial ?? false),
            'centro_custo_filial_id' => $admissao?->centro_custo_filial_id,
            'periodo_aquisitivo_id' => $item->periodo_aquisitivo_id,
            'periodo_label' => $item->PeriodoAquisitivo?->label ?? '',
            'data_saida' => $item->data_saida,
            'data_retorno' => $item->data_retorno,
            'ultima_data' => $item->ultima_data,
            'qnt_dias' => $item->qnt_dias,
            'dias_saldo' => $item->dias_saldo,
            'tem_faltas' => (bool) $item->tem_faltas,
            'qnt_faltas' => $item->qnt_faltas,
            'gestor_id' => $item->gestor_id ?? '',
            'autocomplete_label_gestor_modal' => $gestorNome,
            'autocomplete_label_gestor_modal_anterior' => $gestorNome,
            'obs_solicitante' => $item->obs_solicitante,
            'solicitante' => $item->Solicitante?->nome ?? '',
            'data_solicitacao' => $item->data_solicitacao,
            'anexos' => $this->mapAnexos($item),
            'anexosDel' => [],
            'gestor_aprovacao' => $this->mapUserResumo($item->GestorAprovacao),
            'obs_gestor' => $item->obs_gestor,
            'status_aprovacao_gestor' => $item->status_aprovacao_gestor ?: '',
            'data_aprovacao_gestor' => $item->data_aprovacao_gestor,
            'aprovacao_extra' => $this->mapUserResumo($item->AprovacaoExtra),
            'aprovacao_extra_id' => $item->aprovacao_extra_id,
            'obs_aprovacao_extra' => $item->obs_aprovacao_extra,
            'status_aprovacao_extra' => $item->status_aprovacao_extra ?: '',
            'data_aprovacao_extra' => $item->data_aprovacao_extra,
            'rh_aprovacao' => $this->mapUserResumo($item->RhAprovacao),
            'rh_aprovacao_id' => $item->rh_aprovacao_id,
            'obs_rh' => $item->obs_rh,
            'status_aprovacao_rh' => $item->status_aprovacao_rh ?: '',
            'data_aprovacao_rh' => $item->data_aprovacao_rh,
            'aprovado_via_script' => (bool) $item->aprovado_via_script,
            'abono_pecuniario' => (bool) $item->abono_pecuniario,
            'adiantamento_decimo_terceiro' => (bool) $item->adiantamento_decimo_terceiro,
            'empresa_id' => $item->empresa_id,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapAnexos(Ferias $item): array
    {
        if (!$item->relationLoaded('Anexos')) {
            return [];
        }

        return $item->Anexos->map(function (Arquivo $arquivo) {
            return [
                'id' => (int) $arquivo->id,
                'nome' => $arquivo->nome,
                'file' => $arquivo->file,
                'disco' => $arquivo->disco,
                'imagem' => (bool) $arquivo->imagem,
                'thumb' => $arquivo->thumb,
                'extensao' => $arquivo->extensao,
                'bytes' => $arquivo->bytes,
                'chave' => $arquivo->chave,
                'temporario' => (bool) $arquivo->temporario,
                'url' => $arquivo->url,
                'urlThumb' => $arquivo->urlThumb,
                'urlDownload' => $arquivo->urlDownload,
                'urlDelete' => $arquivo->urlDelete,
            ];
        })->values()->all();
    }

    /**
     * @return array{id:int,nome:string}|null
     */
    private function mapUserResumo(?User $user): ?array
    {
        if (!$user) {
            return null;
        }

        return [
            'id' => (int) $user->id,
            'nome' => (string) $user->nome,
        ];
    }
}
