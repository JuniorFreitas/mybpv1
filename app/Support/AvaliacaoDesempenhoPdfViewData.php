<?php

namespace App\Support;

use App\Models\AvaliacaoFeedback;
use Illuminate\Support\Collection;

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
     * Rótulo da coluna de etapa como no modal PDI (Vue: tituloEtapaFluxoColuna), sem forçar caixa alta.
     *
     * @param  array<string, mixed>  $avaliador
     * @param  list<array<string, mixed>>  $fluxoEtapas
     */
    public static function tituloEtapaFluxoColunaPdf(int $indice, array $avaliador, array $fluxoEtapas): string
    {
        if (($avaliador['tipo'] ?? '') !== '') {
            return trim((string) $avaliador['tipo']);
        }
        if (! empty($fluxoEtapas[$indice]['label'])) {
            return trim((string) $fluxoEtapas[$indice]['label']);
        }
        if (($avaliador['origem'] ?? '') === 'Funcionario') {
            return 'Autoavaliação';
        }

        return 'Avaliador ' . ($indice + 1);
    }

    /** Mesma regra do modal: uma casa decimal ou 0.0 se inválido. */
    public static function formatarDecimalNotaPdf(mixed $valor): string
    {
        if ($valor === null || $valor === '' || ! is_numeric($valor)) {
            return '0.0';
        }

        return number_format((float) $valor, 1, '.', '');
    }

    /** Rótulo da escala (caixa mista), espelhando textoNotaResultado no Vue. */
    public static function textoNotaResultadoPdf(mixed $nota): string
    {
        if ($nota === null || $nota === '' || ! is_numeric($nota)) {
            return 'Sem nota';
        }
        $v = (int) round((float) $nota);

        return match ($v) {
            1 => 'Muito abaixo',
            2 => 'Abaixo',
            3 => 'Atingiu',
            4 => 'Superou',
            5 => 'Superou muito',
            default => 'Sem nota',
        };
    }

    /** Versão em maiúsculas para a segunda linha da célula (como no PDI). */
    public static function textoNotaResultadoMaiusculoPdf(mixed $nota): string
    {
        return mb_strtoupper(self::textoNotaResultadoPdf($nota), 'UTF-8');
    }

    /**
     * Informativo da escala 1–5 para o PDF; textos alinhados ao componente Vue `EscalaAvaliacao`.
     *
     * @return list<array{nota: int, texto: string}>
     */
    public static function itensEscalaInformativoDesempenhoPdf(): array
    {
        return [
            [
                'nota' => 5,
                'texto' => 'Superou muito as expectativas: É percebido por outras áreas/pessoas como alguém com uma atuação excepcional, modelo de referência',
            ],
            [
                'nota' => 4,
                'texto' => 'Superou as expectativas: Atuação melhor que o esperado com alto padrão de qualidade',
            ],
            [
                'nota' => 3,
                'texto' => 'Atingiu as expectativas: Atuação adequada ao esperado (satisfatório), atende os padrões de qualidade e produtividade',
            ],
            [
                'nota' => 2,
                'texto' => 'Abaixo das expectativas: Atuação abaixo do esperado (precisa de desenvolvimento)',
            ],
            [
                'nota' => 1,
                'texto' => 'Muito abaixo das expectativas: Atuação não aceitável, desempenho muito abaixo do que é esperado para a função',
            ],
        ];
    }

    /**
     * Sufixo da classe CSS da célula de nota (1–5 ou neutro), alinhado a classeNotaResultado no Vue.
     */
    public static function sufixoClasseNotaResultadoPdf(mixed $nota): string
    {
        if ($nota === null || $nota === '' || ! is_numeric($nota)) {
            return 'neutro';
        }
        $v = (int) round((float) $nota);
        if ($v >= 1 && $v <= 5) {
            return (string) $v;
        }

        return 'neutro';
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
     * Índice da etapa no array `fluxo_etapas` (retorno de fluxoAvaliacao): id da etapa = avaliacao_tipo_id;
     * autoavaliação = etapa com id 0 (prepend em fluxoAvaliacao).
     *
     * @param  array<string, mixed>  $avaliador  Deve incluir `origem`, opcional `tipo` e `avaliacao_tipo_id`
     * @param  list<array<string, mixed>>  $fluxoEtapas
     */
    public static function indiceEtapaNoFluxoPdf(array $avaliador, array $fluxoEtapas): ?int
    {
        $origem = (string) ($avaliador['origem'] ?? '');
        $isAuto = $origem === AvaliacaoFeedback::ORIGEM_FUNCIONARIO
            || self::normalizarTituloEtapa((string) ($avaliador['tipo'] ?? '')) === 'AUTOAVALIAÇÃO';

        if ($isAuto) {
            foreach ($fluxoEtapas as $i => $etapa) {
                if ((int) ($etapa['id'] ?? -1) === 0) {
                    return (int) $i;
                }
                if (self::normalizarTituloEtapa((string) ($etapa['label'] ?? '')) === 'AUTOAVALIAÇÃO') {
                    return (int) $i;
                }
            }

            return 0;
        }

        $tipoId = (int) ($avaliador['avaliacao_tipo_id'] ?? 0);
        if ($tipoId <= 0) {
            return null;
        }

        foreach ($fluxoEtapas as $i => $etapa) {
            if ((int) ($etapa['id'] ?? -999999) === $tipoId) {
                return (int) $i;
            }
        }

        return null;
    }

    /**
     * Chave de ordenação das colunas no PDF / API: posição no fluxo, com fallback por título (legado).
     *
     * @param  array<string, mixed>  $avaliador
     * @param  list<array<string, mixed>>  $fluxoEtapas
     */
    public static function ordemColunaFluxoPdf(array $avaliador, array $fluxoEtapas, int $indiceFallback): int
    {
        $pos = self::indiceEtapaNoFluxoPdf($avaliador, $fluxoEtapas);
        if ($pos !== null) {
            return $pos;
        }

        return self::ordemFluxoComentario($avaliador, $indiceFallback, $fluxoEtapas);
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
            return self::ordemColunaFluxoPdf($a['avaliador'], $fluxo, $a['indice'])
                <=> self::ordemColunaFluxoPdf($b['avaliador'], $fluxo, $b['indice']);
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
                'ord' => self::ordemColunaFluxoPdf($av, $fluxoEtapas, (int) $i),
            ];
        }
        usort($wrapped, function (array $a, array $b): int {
            return $a['ord'] <=> $b['ord'] ?: $a['origIdx'] <=> $b['origIdx'];
        });

        return array_column($wrapped, 'av');
    }

    /**
     * Mesmos campos usados em {@see AvaliacaoController::avaliarFinal()} para montar cada avaliador no resultado.
     *
     * @return array{tipo: string, origem: string}
     */
    public static function avaliadorOrdemPayloadFromFeedback(AvaliacaoFeedback $item): array
    {
        $tipoAvaliador = 'Avaliador';
        if ($item->origem_feedback === AvaliacaoFeedback::ORIGEM_FUNCIONARIO) {
            $tipoAvaliador = 'Autoavaliação';
        } elseif ($item->TipoAvaliador && $item->TipoAvaliador->label) {
            $tipoAvaliador = (string) $item->TipoAvaliador->label;
            if ($item->principal && ! str_contains($tipoAvaliador, '(Avaliador Final)')) {
                $tipoAvaliador .= ' (Avaliador Final)';
            }
        }

        return [
            'tipo' => $tipoAvaliador,
            'origem' => (string) $item->origem_feedback,
        ];
    }

    public static function ordemFluxoAvaliacaoFeedback(AvaliacaoFeedback $item, int $indiceNoLote, array $fluxoEtapas): int
    {
        $payload = self::avaliadorOrdemPayloadFromFeedback($item);
        $payload['avaliacao_tipo_id'] = $item->avaliacao_tipo_id;

        return self::ordemColunaFluxoPdf($payload, $fluxoEtapas, $indiceNoLote);
    }

    /**
     * Ordena feedbacks do colaborador na mesma sequência do fluxo da avaliação (etapas cadastradas + auto, se houver).
     *
     * @param  Collection<int, AvaliacaoFeedback>  $feedbacks
     * @param  list<array<string, mixed>>  $fluxoEtapas
     * @return Collection<int, AvaliacaoFeedback>
     */
    public static function ordenarFeedbacksPorFluxo(Collection $feedbacks, array $fluxoEtapas): Collection
    {
        $items = $feedbacks->values()->all();
        $wrapped = [];
        foreach ($items as $i => $fb) {
            if (! $fb instanceof AvaliacaoFeedback) {
                continue;
            }
            $wrapped[] = [
                'fb' => $fb,
                'ord' => self::ordemFluxoAvaliacaoFeedback($fb, (int) $i, $fluxoEtapas),
                'idx' => (int) $i,
            ];
        }
        usort($wrapped, function (array $a, array $b): int {
            return $a['ord'] <=> $b['ord'] ?: $a['idx'] <=> $b['idx'];
        });

        return collect(array_column($wrapped, 'fb'));
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
