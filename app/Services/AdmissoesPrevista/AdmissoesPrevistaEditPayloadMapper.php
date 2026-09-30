<?php

namespace App\Services\AdmissoesPrevista;

use App\Models\Admissao;
use App\Models\AdmissoesPrevista;
use App\Models\Arquivo;
use App\Models\User;
use DateTimeInterface;
use MasterTag\DataHora;

/**
 * Monta payload enxuto do modal editar/visualizar/aprovar Admissão Prevista.
 */
class AdmissoesPrevistaEditPayloadMapper
{
    public const ADMISSAO_COLUMNS = [
        'id',
        'colaborador_id',
        'nome_pessoa',
        'centro_custo_id',
        'filial',
        'centro_custo_filial_id',
        'tipo_contrato',
        'cargo_id',
        'data_admissao',
        'salario',
        'user_id',
        'solicitante',
        'obs',
        'user_aprovacao_id',
        'obs_aprovacao',
        'data_aprovacao',
        'status_aprovacao',
        'aprovacao_extra_id',
        'status_aprovacao_extra',
        'obs_aprovacao_extra',
        'data_aprovacao_extra',
        'gestor_id',
        'rh_aprovacao_id',
        'obs_rh',
        'status_aprovacao_rh',
        'data_aprovacao_rh',
        'aprovado_via_script',
        'empresa_id',
    ];

    public const USER_COLUMNS = ['id', 'nome'];

    public const CARGO_COLUMNS = ['id', 'nome'];

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
    public function map(AdmissoesPrevista $item): array
    {
        $gestorNome = $item->GestorAprovacao?->nome ?? '';
        $cargoNome = $item->Cargo?->nome ?? '';
        $tipoContrato = $this->normalizeTipoContrato($item->tipo_contrato);
        $statusGestor = $item->status_aprovacao ?: '';
        $aprovacaoExtraUser = $item->UserAprovacaoExtra;

        return [
            'id' => (int) $item->id,
            'colaborador_id' => $item->colaborador_id,
            'nome_pessoa' => $item->nome_pessoa ?? '',
            'centro_custo_id' => $item->centro_custo_id,
            'filial' => (bool) $item->filial,
            'centro_custo_filial_id' => $item->centro_custo_filial_id,
            'tipo_contrato' => $tipoContrato,
            'cargo_id' => $item->cargo_id,
            'autocomplete_label_cargo' => $cargoNome,
            'autocomplete_label_cargo_anterior' => $cargoNome,
            'data_admissao' => $this->formatDateBr($item->data_admissao),
            'salario' => $item->salario,
            'salario_format' => $item->salario_format,
            'gestor_id' => $item->gestor_id,
            'autocomplete_label_gestor_modal' => $gestorNome,
            'autocomplete_label_gestor_modal_anterior' => $gestorNome,
            'obs' => $item->obs,
            'anexos' => $this->mapAnexos($item),
            'anexosDel' => [],
            'data_aprovacao' => $this->formatDateTimeBr($item->data_aprovacao),
            'obs_aprovacao' => $item->obs_aprovacao,
            'status_aprovacao' => $statusGestor,
            'status_aprovacao_gestor' => $statusGestor !== '' ? ucfirst($statusGestor) : '',
            'user_aprovacao' => $this->mapUserResumo($item->UserAprovacao),
            'data_aprovacao_extra' => $this->formatDateTimeBr($item->data_aprovacao_extra),
            'obs_aprovacao_extra' => $item->obs_aprovacao_extra,
            'status_aprovacao_extra' => $item->status_aprovacao_extra ?: '',
            'aprovacao_extra_id' => $item->aprovacao_extra_id,
            'aprovacao_extra' => $this->mapUserResumo($aprovacaoExtraUser),
            'aprovacao_extra_nome' => $aprovacaoExtraUser?->nome ?? '',
            'data_aprovacao_rh' => $this->formatDateTimeBr($item->data_aprovacao_rh),
            'obs_rh' => $item->obs_rh,
            'status_aprovacao_rh' => $item->status_aprovacao_rh ?: '',
            'rh_aprovacao' => $this->mapUserResumo($item->RhAprovacao),
            'rh_aprovacao_id' => $item->rh_aprovacao_id,
            'aprovado_via_script' => (bool) $item->aprovado_via_script,
            'empresa_id' => $item->empresa_id,
        ];
    }

    private function formatDateBr(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        $texto = trim((string) $value);
        if ($texto === '' || $texto === 'Invalid date') {
            return '';
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $texto)) {
            return $texto;
        }

        return (new DataHora($texto))->dataCompleta();
    }

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

    private function normalizeTipoContrato(?string $tipo): string
    {
        if ($tipo === null || $tipo === '') {
            return '';
        }

        $map = [
            'Fixo' => Admissao::TIPO_ADMISSAO_FIXO,
            'Intermitente' => Admissao::TIPO_ADMISSAO_INTERMITENTE,
            'Aprendiz' => Admissao::TIPO_ADMISSAO_APRENDIZ,
        ];

        if (isset($map[$tipo])) {
            return $map[$tipo];
        }

        return in_array($tipo, Admissao::TODOS_TIPOS_ADMISSAO, true)
            ? $tipo
            : Admissao::TIPO_ADMISSAO_FIXO;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapAnexos(AdmissoesPrevista $item): array
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
