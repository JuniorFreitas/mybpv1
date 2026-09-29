<?php

namespace App\Services\MudaCargoPrevista;

use App\Models\Arquivo;
use App\Models\MudaCargoPrevista;
use App\Models\User;

/**
 * Monta payload enxuto do modal editar/visualizar Mudança de Cargo Prevista (legado).
 */
class MudaCargoPrevistaEditPayloadMapper
{
    public const MUDA_CARGO_COLUMNS = [
        'id',
        'colaborador_id',
        'centro_custo_id',
        'cargo_anterior_id',
        'salario_anterior',
        'novo_cargo_id',
        'novo_salario',
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
        'empresa_id',
        'user_id',
    ];

    public const USER_COLUMNS = ['id', 'nome'];

    public const VAGA_COLUMNS = ['id', 'nome'];

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
    public function map(MudaCargoPrevista $item): array
    {
        $colaboradorNome = $item->Colaborador?->nome ?? '';
        $gestorNome = $item->GestorAprovacao?->nome ?? '';
        $cargoAnteriorNome = $item->CargoAnterior?->nome ?? '';
        $novoCargoNome = $item->NovoCargo?->nome ?? '';

        return [
            'id' => (int) $item->id,
            'colaborador_id' => $item->colaborador_id,
            'centro_custo_id' => $item->centro_custo_id,
            'autocomplete_label_colaborador' => $colaboradorNome,
            'autocomplete_label_colaborador_anterior' => $colaboradorNome,
            'cargo_anterior_id' => $item->cargo_anterior_id,
            'autocomplete_label_cargoanterior' => $cargoAnteriorNome,
            'autocomplete_label_cargoanterior_anterior' => $cargoAnteriorNome,
            'salario_anterior' => $item->salario_anterior,
            'salario_anterior_format' => $item->salario_anterior_format,
            'novo_cargo_id' => $item->novo_cargo_id,
            'autocomplete_label_novo_cargo' => $novoCargoNome,
            'autocomplete_label_novo_cargo_anterior' => $novoCargoNome,
            'novo_salario' => $item->novo_salario,
            'novo_salario_format' => $item->novo_salario_format,
            'gestor_id' => $item->gestor_id,
            'autocomplete_label_gestor_modal' => $gestorNome,
            'autocomplete_label_gestor_modal_anterior' => $gestorNome,
            'obs' => $item->obs,
            'anexos' => $this->mapAnexos($item),
            'anexosDel' => [],
            'data_aprovacao' => $item->data_aprovacao,
            'obs_aprovacao' => $item->obs_aprovacao,
            'status_aprovacao' => $item->status_aprovacao ?: '',
            'user_aprovacao' => $this->mapUserResumo($item->UserAprovacao),
            'aprovacao_extra_id' => $item->aprovacao_extra_id,
            'status_aprovacao_extra' => $item->status_aprovacao_extra ?: '',
            'obs_aprovacao_extra' => $item->obs_aprovacao_extra,
            'data_aprovacao_extra' => $item->data_aprovacao_extra,
            'aprovacao_extra' => $this->mapUserResumo($item->AprovacaoExtra),
            'empresa_id' => $item->empresa_id,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapAnexos(MudaCargoPrevista $item): array
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
