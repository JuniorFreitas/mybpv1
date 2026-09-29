<?php

namespace App\Services\ValorExtraPrevista;

use App\Models\Arquivo;
use App\Models\User;
use App\Models\ValorExtraPrevista;

/**
 * Monta payload enxuto do modal editar/visualizar/aprovar Valor Extra Prevista.
 */
class ValorExtraPrevistaEditPayloadMapper
{
    public const VALOR_EXTRA_COLUMNS = [
        'id',
        'colaborador_id',
        'centro_custo_id',
        'filial',
        'centro_custo_filial_id',
        'tipo',
        'periodo_dias',
        'gestor_id',
        'obs',
        'user_id',
        'solicitante',
        'user_aprovacao_id',
        'obs_aprovacao',
        'data_aprovacao',
        'status_aprovacao',
        'aprovacao_extra_id',
        'status_aprovacao_extra',
        'obs_aprovacao_extra',
        'data_aprovacao_extra',
        'rh_aprovacao_id',
        'obs_rh',
        'status_aprovacao_rh',
        'data_aprovacao_rh',
        'aprovado_via_script',
        'empresa_id',
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
    public function map(ValorExtraPrevista $item): array
    {
        $colaboradorNome = $item->Colaborador?->nome ?? '';
        $gestorNome = $item->GestorAprovacao?->nome ?? '';

        return [
            'id' => (int) $item->id,
            'colaborador_id' => $item->colaborador_id,
            'autocomplete_label_colaborador' => $colaboradorNome,
            'autocomplete_label_colaborador_anterior' => $colaboradorNome,
            'centro_custo_id' => $item->centro_custo_id,
            'filial' => (bool) $item->filial,
            'centro_custo_filial_id' => $item->centro_custo_filial_id,
            'tipo' => $item->tipo,
            'periodo_dias' => $item->periodo_dias,
            'gestor_id' => $item->gestor_id,
            'autocomplete_label_gestor_modal' => $gestorNome,
            'autocomplete_label_gestor_modal_anterior' => $gestorNome,
            'obs' => $item->obs,
            'user_id' => $item->user_id,
            'solicitante' => $item->solicitante,
            'anexos' => $this->mapAnexos($item),
            'anexosDel' => [],
            'data_aprovacao' => $item->data_aprovacao,
            'obs_aprovacao' => $item->obs_aprovacao,
            'status_aprovacao' => $item->status_aprovacao ?: '',
            'user_aprovacao' => $this->mapUserResumo($item->UserAprovacao),
            'data_aprovacao_extra' => $item->data_aprovacao_extra,
            'obs_aprovacao_extra' => $item->obs_aprovacao_extra,
            'status_aprovacao_extra' => $item->status_aprovacao_extra ?: '',
            'aprovacao_extra_id' => $item->aprovacao_extra_id,
            'aprovacao_extra_nome' => $item->AprovacaoExtra?->nome ?? '',
            'data_aprovacao_rh' => $item->data_aprovacao_rh,
            'obs_rh' => $item->obs_rh,
            'status_aprovacao_rh' => $item->status_aprovacao_rh ?: '',
            'rh_aprovacao' => $this->mapUserResumo($item->RhAprovacao),
            'rh_aprovacao_id' => $item->rh_aprovacao_id,
            'aprovado_via_script' => (bool) $item->aprovado_via_script,
            'empresa_id' => $item->empresa_id,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapAnexos(ValorExtraPrevista $item): array
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
