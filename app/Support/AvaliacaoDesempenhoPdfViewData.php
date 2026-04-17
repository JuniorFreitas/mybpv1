<?php

namespace App\Support;

/**
 * Dados derivados para o PDF de desempenho (ordem de comentários e títulos de etapa),
 * espelhando a lógica usada na impressão via Vue (resources/js/g/impressao/avaliacao/app.js).
 */
final class AvaliacaoDesempenhoPdfViewData
{
    public static function normalizarTituloEtapa(?string $titulo): string
    {
        $valor = mb_strtoupper(trim((string) $titulo), 'UTF-8');
        if (preg_match('/^AUTO\s*AVALIA[CÇ][AÃ]O$/u', $valor) === 1) {
            return 'AUTOAVALIAÇÃO';
        }

        return $valor;
    }

    /**
     * @param  array<string, mixed>  $avaliador
     * @param  list<array<string, mixed>>  $fluxoEtapas
     */
    public static function tituloEtapaFluxoPdf(int $indice, array $avaliador, array $fluxoEtapas): string
    {
        if (! empty($avaliador['tipo'])) {
            return self::normalizarTituloEtapa((string) $avaliador['tipo']);
        }
        if (! empty($fluxoEtapas[$indice]['label'])) {
            return self::normalizarTituloEtapa((string) $fluxoEtapas[$indice]['label']);
        }
        if (($avaliador['origem'] ?? '') === 'Funcionario') {
            return 'AUTOAVALIAÇÃO';
        }

        return 'AVALIADOR ' . ($indice + 1);
    }

    /**
     * @param  array<string, mixed>  $avaliador
     * @param  list<array<string, mixed>>  $fluxoEtapas
     */
    public static function tituloConsideracoesPdf(int $indice, array $avaliador, array $fluxoEtapas): string
    {
        $titulo = self::tituloEtapaFluxoPdf($indice, $avaliador, $fluxoEtapas);

        return $titulo === 'AUTOAVALIAÇÃO'
            ? 'CONSIDERAÇÕES DA AUTOAVALIAÇÃO'
            : 'CONSIDERAÇÕES DO ' . $titulo;
    }

    /**
     * @param  array<string, mixed>  $avaliador
     * @param  list<array<string, mixed>>  $fluxoEtapas
     */
    public static function ordemFluxoComentario(array $avaliador, int $indice, array $fluxoEtapas): int
    {
        $titulo = self::tituloEtapaFluxoPdf($indice, $avaliador, $fluxoEtapas);
        if ($titulo === 'AUTOAVALIAÇÃO') {
            return -1;
        }
        foreach ($fluxoEtapas as $i => $etapa) {
            if (self::normalizarTituloEtapa($etapa['label'] ?? '') === $titulo) {
                return $i;
            }
        }

        return 999 + $indice;
    }

    /**
     * @param  array<string, mixed>  $dados  Retorno de avaliarFinal()
     * @return list<array{indice: int, avaliador: array<string, mixed>}>
     */
    public static function comentariosOrdenados(array $dados): array
    {
        $agrupado = $dados['result_topico_pai_agrupado'] ?? [];
        $primeiroGrupo = collect($agrupado)->first();
        if ($primeiroGrupo === null) {
            return [];
        }
        $primeiraLinha = collect($primeiroGrupo)->first();
        if (! is_array($primeiraLinha)) {
            return [];
        }
        $avaliadores = $primeiraLinha['avaliadores'] ?? [];
        if (! is_array($avaliadores)) {
            return [];
        }
        $fluxo = $dados['fluxo_etapas'] ?? [];
        if (! is_array($fluxo)) {
            $fluxo = [];
        }

        $items = [];
        foreach ($avaliadores as $indice => $avaliador) {
            if (! is_array($avaliador)) {
                continue;
            }
            if (trim((string) ($avaliador['comentario'] ?? '')) === '') {
                continue;
            }
            $items[] = ['indice' => (int) $indice, 'avaliador' => $avaliador];
        }

        usort($items, function (array $a, array $b) use ($fluxo): int {
            return self::ordemFluxoComentario($a['avaliador'], $a['indice'], $fluxo)
                <=> self::ordemFluxoComentario($b['avaliador'], $b['indice'], $fluxo);
        });

        return $items;
    }

    /**
     * Ordena resultados do PDF como na avaliação: critérios por id do tópico, grupos (competências-pai)
     * na ordem em que os critérios aparecem, e gráficos alinhados a essa ordem.
     *
     * @param  array<string, mixed>  $dados  Retorno de avaliarFinal()
     * @return array<string, mixed>
     */
    /**
     * @param  list<array<string, mixed>>  $avaliadores
     * @param  list<array<string, mixed>>  $fluxoEtapas
     * @return list<array<string, mixed>>
     */
    public static function ordenarAvaliadoresPorFluxo(array $avaliadores, array $fluxoEtapas): array
    {
        $wrapped = [];
        foreach ($avaliadores as $i => $av) {
            if (! is_array($av)) {
                continue;
            }
            $wrapped[] = [
                'origIdx' => $i,
                'av' => $av,
                'ord' => self::ordemFluxoComentario($av, (int) $i, $fluxoEtapas),
            ];
        }
        usort($wrapped, function (array $a, array $b): int {
            return $a['ord'] <=> $b['ord'] ?: $a['origIdx'] <=> $b['origIdx'];
        });

        return array_column($wrapped, 'av');
    }

    public static function ordenarDadosParaPdf(array $dados): array
    {
        $fluxo = $dados['fluxo_etapas'] ?? [];
        $fluxo = is_array($fluxo) ? $fluxo : [];

        $sortedRows = collect($dados['result_topico'] ?? [])
            ->map(function ($row) {
                return is_array($row) ? $row : (array) $row;
            })
            ->sortBy(fn (array $row): int => (int) ($row['topico_id'] ?? 0))
            ->values()
            ->map(function (array $row) use ($fluxo): array {
                $av = $row['avaliadores'] ?? null;
                if ($av instanceof \Illuminate\Support\Collection) {
                    $av = $av->values()->all();
                }
                if (! is_array($av) || $av === []) {
                    return $row;
                }
                $row['avaliadores'] = self::ordenarAvaliadoresPorFluxo($av, $fluxo);

                return $row;
            });

        $dados['result_topico'] = $sortedRows;

        $parentIds = $sortedRows->pluck('topico_pai_id')->unique()->values();
        $agrupado = [];
        foreach ($parentIds as $paiId) {
            $slice = $sortedRows->filter(function (array $row) use ($paiId): bool {
                return (string) ($row['topico_pai_id'] ?? '') === (string) $paiId;
            })->values()->all();
            if ($slice !== []) {
                $agrupado[] = $slice;
            }
        }
        $dados['result_topico_pai_agrupado'] = $agrupado;

        $orderMap = [];
        foreach ($parentIds as $index => $paiId) {
            $first = $sortedRows->first(function (array $row) use ($paiId): bool {
                return (string) ($row['topico_pai_id'] ?? '') === (string) $paiId;
            });
            if ($first !== null && isset($first['topico_pai'])) {
                $orderMap[(string) $first['topico_pai']] = $index;
            }
        }

        $dados['resultChart'] = collect($dados['resultChart'] ?? [])
            ->sortBy(function ($chart) use ($orderMap): int {
                $name = (string) (($chart ?? [])['name'] ?? '');

                return $orderMap[$name] ?? 9999;
            })
            ->values()
            ->all();

        return $dados;
    }
}
