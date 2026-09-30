<?php

namespace App\Services\DemissaoPrevista;

use App\Models\Arquivo;
use App\Models\DemissaoPrevista;
use App\Models\User;
use DateTimeInterface;
use MasterTag\DataHora;

/**
 * Monta payload enxuto do modal editar/visualizar/aprovar Demissão Prevista.
 */
class DemissaoPrevistaEditPayloadMapper
{
    public const DEMISSAO_COLUMNS = [
        'id',
        'colaborador_id',
        'centro_custo_id',
        'filial',
        'centro_custo_filial_id',
        'data_demissao',
        'tipo_aviso',
        'valor',
        'gestor_id',
        'obs',
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
    public function map(DemissaoPrevista $item): array
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
            'data_demissao' => $this->formatDateBr($item->data_demissao),
            'tipo_aviso' => $item->tipo_aviso,
            'valor' => $item->valor,
            'valor_format' => $item->valor_format,
            'gestor_id' => $item->gestor_id,
            'autocomplete_label_gestor_modal' => $gestorNome,
            'autocomplete_label_gestor_modal_anterior' => $gestorNome,
            'obs' => $item->obs,
            'anexos' => $this->mapAnexos($item),
            'anexosDel' => [],
            'data_aprovacao' => $this->formatDateBr($item->data_aprovacao),
            'obs_aprovacao' => $item->obs_aprovacao,
            'status_aprovacao' => $item->status_aprovacao ?: '',
            'user_aprovacao' => $this->mapUserResumo($item->UserAprovacao),
            'data_aprovacao_extra' => $this->formatDateTimeBr($item->data_aprovacao_extra),
            'obs_aprovacao_extra' => $item->obs_aprovacao_extra,
            'status_aprovacao_extra' => $item->status_aprovacao_extra ?: '',
            'aprovacao_extra' => $this->mapUserResumo($item->AprovacaoExtra),
            'aprovacao_extra_id' => $item->aprovacao_extra_id,
            'data_aprovacao_rh' => $this->formatDateTimeBr($item->data_aprovacao_rh),
            'obs_rh' => $item->obs_rh,
            'status_aprovacao_rh' => $item->status_aprovacao_rh ?: '',
            'rh_aprovacao' => $this->mapUserResumo($item->RhAprovacao),
            'rh_aprovacao_id' => $item->rh_aprovacao_id,
            'aprovado_via_script' => (bool) $item->aprovado_via_script,
            'empresa_id' => $item->empresa_id,
        ];
    }

    /**
     * DatePicker e labels do modal esperam d/m/Y (não ISO/Carbon).
     */
    private function formatDateBr(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        $texto = trim((string) $value);
        if ($texto === '') {
            return '';
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $texto)) {
            return $texto;
        }

        return (new DataHora($texto))->dataCompleta();
    }

    /**
     * Datas de aprovação no modal: d/m/Y às H:i:s (padrão da listagem).
     */
    private function formatDateTimeBr(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y \à\s H:i:s');
        }

        $texto = trim((string) $value);
        if ($texto === '') {
            return '';
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}/', $texto)) {
            return $texto;
        }

        $data = new DataHora($texto);

        return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto() . ':' . $data->segundo();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapAnexos(DemissaoPrevista $item): array
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
