<?php

namespace App\Domain\Ajuda\Services;

use App\Domain\Ajuda\ManualTrecho;

/**
 * Busca por palavra-chave (BM25) nos trechos do manual.
 *
 * Nomes de menus, botões e siglas ("CIH", "ASO", "Perímetro") são termos
 * exatos, então a busca lexical resolve bem uma base deste tamanho sem
 * depender de embeddings nem gastar cota do provedor de IA. Os sinônimos do
 * GlossarioRh cobrem o caso em que o usuário usa outra palavra para o mesmo assunto.
 */
final class BuscadorTrechosManual
{
    private const K1 = 1.4;

    private const B = 0.75;

    /** Peso dos títulos e do caminho de menu em relação ao corpo do trecho. */
    private const PESO_CABECALHO = 3;

    /** Um sinônimo vale menos que a palavra que o usuário digitou. */
    private const PESO_SINONIMO = 0.5;

    /** Trechos com pontuação muito abaixo do melhor resultado só gastam contexto. */
    private const FRACAO_MINIMA_DO_MELHOR = 0.25;

    /** @var array{chave: string, frequencias: array, tamanhos: array, documentosPorTermo: array, tamanhoMedio: float}|null */
    private ?array $indice = null;

    public function __construct(
        private readonly TokenizadorManual $tokenizador,
        private readonly GlossarioRh $glossario,
    ) {
    }

    /**
     * @param  array<int, ManualTrecho>  $trechos
     * @return array<int, ManualTrecho>
     */
    public function buscar(string $consulta, array $trechos, int $limite = 6): array
    {
        $termos = $this->termosDaConsulta($consulta);
        if ($termos === [] || $trechos === []) {
            return [];
        }

        $indice = $this->indice($trechos);
        $total = count($trechos);

        $pontuacoes = [];
        foreach (array_keys($trechos) as $posicao) {
            $pontuacao = 0.0;
            foreach ($termos as $termo => $peso) {
                $frequencia = $indice['frequencias'][$posicao][$termo] ?? 0;
                if ($frequencia === 0) {
                    continue;
                }

                $df = $indice['documentosPorTermo'][$termo];
                $idf = log(1 + ($total - $df + 0.5) / ($df + 0.5));
                $normalizacao = $frequencia + self::K1 * (1 - self::B + self::B * $indice['tamanhos'][$posicao] / $indice['tamanhoMedio']);
                $pontuacao += $peso * $idf * ($frequencia * (self::K1 + 1)) / $normalizacao;
            }

            if ($pontuacao > 0) {
                $pontuacoes[$posicao] = $pontuacao;
            }
        }

        if ($pontuacoes === []) {
            return [];
        }

        arsort($pontuacoes);
        $corte = reset($pontuacoes) * self::FRACAO_MINIMA_DO_MELHOR;

        $resultado = [];
        foreach ($pontuacoes as $posicao => $pontuacao) {
            if ($pontuacao < $corte || count($resultado) >= $limite) {
                break;
            }
            $resultado[] = $trechos[$posicao];
        }

        return $resultado;
    }

    /**
     * Separa os termos da consulta entre os que existem no manual e os que não existem.
     * Só os primeiros podem ser registrados em log: um termo que não está no manual
     * pode ser um nome, um documento ou outro dado pessoal digitado pelo usuário.
     *
     * @param  array<int, ManualTrecho>  $trechos
     * @return array{conhecidos: array<int, string>, desconhecidos: int}
     */
    public function termosNoManual(string $consulta, array $trechos): array
    {
        $termos = array_values(array_unique($this->termos($consulta)));
        $vocabulario = $trechos === [] ? [] : $this->indice($trechos)['documentosPorTermo'];

        $conhecidos = array_values(array_filter($termos, static fn (string $termo) => isset($vocabulario[$termo])));

        return [
            'conhecidos' => $conhecidos,
            'desconhecidos' => count($termos) - count($conhecidos),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function termos(string $texto): array
    {
        return $this->glossario->canonizar($this->tokenizador->tokenizar($texto));
    }

    /**
     * @return array<string, float> termo → peso
     */
    private function termosDaConsulta(string $consulta): array
    {
        $termos = [];
        foreach ($this->termos($consulta) as $termo) {
            $termos[$termo] = 1.0;
        }

        foreach (array_keys($termos) as $termo) {
            foreach ($this->glossario->sinonimos($termo) as $sinonimo) {
                $termos[$sinonimo] ??= self::PESO_SINONIMO;
            }
        }

        return $termos;
    }

    /**
     * O índice é montado uma vez por conjunto de trechos e reaproveitado na mesma requisição.
     *
     * @param  array<int, ManualTrecho>  $trechos
     */
    private function indice(array $trechos): array
    {
        $chave = count($trechos).':'.spl_object_id(reset($trechos));
        if ($this->indice !== null && $this->indice['chave'] === $chave) {
            return $this->indice;
        }

        $frequencias = [];
        $tamanhos = [];
        $documentosPorTermo = [];

        foreach ($trechos as $posicao => $trecho) {
            $tokens = array_merge(
                $this->termos($trecho->texto),
                ...array_fill(0, self::PESO_CABECALHO, $this->termos($trecho->cabecalho())),
            );

            $frequencias[$posicao] = array_count_values($tokens);
            $tamanhos[$posicao] = count($tokens);

            foreach (array_keys($frequencias[$posicao]) as $termo) {
                $documentosPorTermo[$termo] = ($documentosPorTermo[$termo] ?? 0) + 1;
            }
        }

        return $this->indice = [
            'chave' => $chave,
            'frequencias' => $frequencias,
            'tamanhos' => $tamanhos,
            'documentosPorTermo' => $documentosPorTermo,
            'tamanhoMedio' => max(1, array_sum($tamanhos) / count($trechos)),
        ];
    }
}
