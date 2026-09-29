<?php

namespace App\Services\Planejamento\Movimentacao;

use App\Models\CentroCustoFilial;
use App\Models\Cliente;
use App\Models\ClienteFilial;
use App\Models\MudancaCargo;
use App\Services\Cih\CihLotacaoResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Monta rótulo de Lotação (nome fantasia + CNPJ) para cards de movimentação.
 */
class LotacaoLabelResolver
{
    public static function format(?string $nomeFantasia, ?string $razaoSocial, ?string $cnpj): string
    {
        $nome = trim((string) ($nomeFantasia ?: $razaoSocial));
        $cnpj = trim((string) $cnpj);
        if ($nome === '' && $cnpj === '') {
            return 'Não informado';
        }
        if ($nome === '') {
            return $cnpj;
        }
        if ($cnpj === '') {
            return $nome;
        }

        return $nome . ' - ' . $cnpj;
    }

    public static function fromEmpresa(?Cliente $empresa): string
    {
        if (!$empresa) {
            return 'Não informado';
        }

        return self::format($empresa->nome_fantasia, $empresa->razao_social, $empresa->cnpj);
    }

    public static function fromFilial(?ClienteFilial $filial): string
    {
        if (!$filial) {
            return 'Não informado';
        }

        $dados = $filial->dados;

        return self::format(
            self::dadosField($dados, 'nome_fantasia'),
            self::dadosField($dados, 'razao_social'),
            self::dadosField($dados, 'cnpj')
        );
    }

    /**
     * Resolve lotação a partir de flags matriz/filial + relações já carregadas.
     *
     * Se existe ClienteFilial vinculada, a lotação dela sempre prevalece
     * (nunca substitui pela matriz).
     */
    public static function resolve(
        bool $ehFilial,
        ?ClienteFilial $filial,
        ?Cliente $empresaMatriz
    ): string {
        if ($filial !== null) {
            $label = self::fromFilial($filial);
            if ($label !== 'Não informado') {
                return $label;
            }
        }

        if ($ehFilial) {
            return 'Não informado';
        }

        return self::fromEmpresa($empresaMatriz);
    }

    public static function forCentroCustoFilialFlags(
        bool|int|string|null $filial,
        ?CentroCustoFilial $centroCustoFilial,
        ?Cliente $empresaMatriz
    ): string {
        $ehFilial = filter_var($filial, FILTER_VALIDATE_BOOLEAN);

        return self::resolve(
            $ehFilial,
            $centroCustoFilial?->Filial,
            $empresaMatriz
        );
    }

    public static function forMudancaCargo(MudancaCargo $item, array $filialMap, ?Cliente $empresaMatriz): string
    {
        $mantemCentro = filter_var($item->mantem_centro_custo ?? true, FILTER_VALIDATE_BOOLEAN);
        $ehFilial = filter_var(
            $mantemCentro ? $item->anterior_filial : $item->novo_filial,
            FILTER_VALIDATE_BOOLEAN
        );
        $ccfId = (int) ($mantemCentro ? $item->anterior_centro_custo_filial_id : $item->novo_centro_custo_filial_id);

        return self::resolve($ehFilial, $filialMap[$ccfId] ?? null, $empresaMatriz);
    }

    public static function fromCentroCustoId(
        int $empresaId,
        ?int $centroCustoId,
        ?Cliente $empresaMatriz,
        ?string $centroCustoLabel = null,
        ?CihLotacaoResolver $resolver = null
    ): string {
        $resolver ??= new CihLotacaoResolver($empresaId);

        if ($centroCustoId) {
            $raw = $resolver->format((int) $centroCustoId);
            if ($raw !== '') {
                $normalized = trim((string) preg_replace('/\s*\((Matriz|Filial)\)\s*$/u', '', $raw));
                if ($normalized !== '') {
                    return $normalized;
                }
            }
        }

        $labelCc = trim((string) $centroCustoLabel);
        if ($labelCc !== '') {
            $empresaLabel = self::fromEmpresa($empresaMatriz);
            if ($empresaLabel !== 'Não informado') {
                return $labelCc . ' — ' . $empresaLabel;
            }

            return $labelCc;
        }

        return self::fromEmpresa($empresaMatriz);
    }

    /**
     * Mapa centro_custo_filial_id => ClienteFilial (batch para listagens).
     *
     * @param  list<int|string|null>  $centroCustoFilialIds
     * @return array<int, ClienteFilial>
     */
    public static function filiaisByCentroCustoFilialIds(array $centroCustoFilialIds): array
    {
        $ids = collect($centroCustoFilialIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return [];
        }

        /** @var Collection<int, int> $ccfToClienteFilial */
        $ccfToClienteFilial = DB::table('centro_custo_filials')
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->pluck('cliente_filial_id', 'id');

        $clienteFilialIds = $ccfToClienteFilial->values()->unique()->filter()->all();
        if ($clienteFilialIds === []) {
            return [];
        }

        $filiais = ClienteFilial::query()
            ->select(['id', 'dados'])
            ->whereIn('id', $clienteFilialIds)
            ->get()
            ->keyBy('id');

        $map = [];
        foreach ($ccfToClienteFilial as $ccfId => $clienteFilialId) {
            $filial = $filiais->get((int) $clienteFilialId);
            if ($filial) {
                $map[(int) $ccfId] = $filial;
            }
        }

        return $map;
    }

    private static function dadosField(mixed $dados, string $field): ?string
    {
        if (is_array($dados)) {
            $value = $dados[$field] ?? null;
        } elseif (is_object($dados)) {
            $value = $dados->{$field} ?? null;
        } else {
            return null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }
}
