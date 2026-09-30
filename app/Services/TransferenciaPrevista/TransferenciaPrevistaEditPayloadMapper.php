<?php

namespace App\Services\TransferenciaPrevista;

use App\Models\Arquivo;
use App\Models\TransferenciaPrevista;
use App\Models\User;
use DateTimeInterface;
use MasterTag\DataHora;

/**
 * Monta payload enxuto do modal editar/visualizar/aprovar Transferência Prevista.
 */
class TransferenciaPrevistaEditPayloadMapper
{
    public const TRANSFERENCIA_COLUMNS = [
        'id',
        'colaborador_id',
        'centro_custo_origem_id',
        'centro_custo_destino_id',
        'data_transferencia',
        'user_id',
        'solicitante',
        'obs',
        'gestor_id',
        'gestor_destino_id',
        'exige_aprovacao_gestor_destino',
        'fluxo_gestores_automatico',
        'modo_aprovacao',
        'gestor_aprovacao_id',
        'user_aprovacao_id',
        'data_aprovacao',
        'obs_aprovacao',
        'status_aprovacao',
        'user_aprovacao_gestor_destino_id',
        'data_aprovacao_gestor_destino',
        'obs_aprovacao_gestor_destino',
        'status_aprovacao_gestor_destino',
        'user_aprovacao_gestor_unico_id',
        'data_aprovacao_gestor_unico',
        'obs_aprovacao_gestor_unico',
        'status_aprovacao_gestor_unico',
        'aprovacao_extra_id',
        'status_aprovacao_extra',
        'obs_aprovacao_extra',
        'data_aprovacao_extra',
        'user_rh_id',
        'resposta_rh',
        'obs_rh',
        'data_aprovacao_rh',
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
    public function map(TransferenciaPrevista $item): array
    {
        $colaboradorNome = $item->Colaborador?->nome ?? '';
        $admissao = $item->Colaborador?->FeedBack?->Admissao;

        return [
            'id' => (int) $item->id,
            'colaborador_id' => $item->colaborador_id,
            'autocomplete_label_colaborador' => $colaboradorNome,
            'autocomplete_label_colaborador_anterior' => $colaboradorNome,
            'centro_custo_id' => $admissao?->centro_custo_id,
            'centro_custo_origem_id' => $item->centro_custo_origem_id,
            'centro_custo_destino_id' => $item->centro_custo_destino_id,
            'data_transferencia' => $this->formatDateBr($item->data_transferencia),
            'user_id' => $item->user_id,
            'solicitante' => $item->solicitante,
            'obs' => $item->obs,
            'gestor_id' => $item->gestor_id,
            'gestor_destino_id' => $item->gestor_destino_id,
            'label_gestor_origem' => $item->GestorOrigem?->nome ?? 'Não informado',
            'label_gestor_destino' => $item->GestorDestino?->nome ?? 'Não informado',
            'label_gestor_aprovacao_unico' => $item->GestorAprovacaoUnico?->nome ?? 'Não informado',
            'modo_aprovacao' => $item->modo_aprovacao ?? TransferenciaPrevista::MODO_APROVACAO_PADRAO,
            'exige_aprovacao_gestor_destino' => (bool) $item->exige_aprovacao_gestor_destino,
            'fluxo_gestores_automatico' => (bool) $item->fluxo_gestores_automatico,
            'gestor_aprovacao_id' => $item->gestor_aprovacao_id,
            'anexos' => $this->mapAnexos($item),
            'anexosDel' => [],
            'data_aprovacao' => $this->formatDateBr($item->data_aprovacao),
            'obs_aprovacao' => $item->obs_aprovacao,
            'status_aprovacao' => $item->status_aprovacao ?: '',
            'user_aprovacao' => $this->mapUserResumo($item->UserAprovacao),
            'user_aprovacao_id' => $item->user_aprovacao_id,
            'data_aprovacao_gestor_destino' => $this->formatDateTimeBr($item->data_aprovacao_gestor_destino),
            'obs_aprovacao_gestor_destino' => $item->obs_aprovacao_gestor_destino,
            'status_aprovacao_gestor_destino' => $item->status_aprovacao_gestor_destino ?: '',
            'quem_aprovou_gestor_destino' => $this->mapUserResumo($item->QuemAprovouGestorDestino),
            'user_aprovacao_gestor_destino_id' => $item->user_aprovacao_gestor_destino_id,
            'data_aprovacao_gestor_unico' => $this->formatDateTimeBr($item->data_aprovacao_gestor_unico),
            'obs_aprovacao_gestor_unico' => $item->obs_aprovacao_gestor_unico,
            'status_aprovacao_gestor_unico' => $item->status_aprovacao_gestor_unico ?: '',
            'quem_aprovou_gestor_unico' => $this->mapUserResumo($item->QuemAprovouGestorUnico),
            'user_aprovacao_gestor_unico_id' => $item->user_aprovacao_gestor_unico_id,
            'data_aprovacao_extra' => $this->formatDateTimeBr($item->data_aprovacao_extra),
            'obs_aprovacao_extra' => $item->obs_aprovacao_extra,
            'status_aprovacao_extra' => $item->status_aprovacao_extra ?: '',
            'aprovacao_extra_id' => $item->aprovacao_extra_id,
            'aprovacao_extra' => $this->mapUserResumo($item->AprovacaoExtra),
            'data_aprovacao_rh' => $this->formatDateTimeBr($item->data_aprovacao_rh),
            'obs_rh' => $item->obs_rh,
            'resposta_rh' => $item->resposta_rh ?: '',
            'rh_aprovacao' => $this->mapUserResumo($item->RhAprovacao),
            'user_rh_id' => $item->user_rh_id,
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
     * Datas de aprovação no modal: d/m/Y às H:i:s.
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
    private function mapAnexos(TransferenciaPrevista $item): array
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
