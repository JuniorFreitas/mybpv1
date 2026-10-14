<?php

namespace App\Domain\Ajuda\Services;

use App\Domain\Ajuda\ManualTrecho;

/**
 * Completa os trechos encontrados com as demais subseções das telas mais
 * relevantes, para a resposta sair inteira (filtros, cadastro e ações da
 * mesma tela) em vez de depender de um único pedaço.
 */
final class MontadorContextoManual
{
    /** Quantas telas (seções) entre as melhores recebem as subseções vizinhas. */
    private const SECOES_EXPANDIDAS = 2;

    /** Teto do contexto enviado ao provedor (~4 mil tokens), para preservar a cota gratuita. */
    private const MAX_CARACTERES = 16000;

    /**
     * @param  array<int, ManualTrecho>  $encontrados  em ordem de relevância
     * @param  array<int, ManualTrecho>  $todos  na ordem do manual
     * @return array<int, ManualTrecho> agrupados por seção, na ordem do manual dentro de cada seção
     */
    public function montar(array $encontrados, array $todos): array
    {
        if ($encontrados === []) {
            return [];
        }

        $selecionados = [];
        $caracteres = 0;
        foreach ($encontrados as $trecho) {
            $selecionados[$trecho->id] = $trecho;
            $caracteres += strlen($trecho->texto);
        }

        $ancoras = array_values(array_unique(array_map(static fn (ManualTrecho $trecho) => $trecho->ancora, $encontrados)));

        foreach (array_slice($ancoras, 0, self::SECOES_EXPANDIDAS) as $ancora) {
            foreach ($todos as $trecho) {
                if ($trecho->ancora !== $ancora || isset($selecionados[$trecho->id])) {
                    continue;
                }
                if ($caracteres + strlen($trecho->texto) > self::MAX_CARACTERES) {
                    continue;
                }

                $selecionados[$trecho->id] = $trecho;
                $caracteres += strlen($trecho->texto);
            }
        }

        $ordemDoManual = array_flip(array_map(static fn (ManualTrecho $trecho) => $trecho->id, $todos));
        $ordemDaSecao = array_flip($ancoras);

        $resultado = array_values($selecionados);
        usort($resultado, static fn (ManualTrecho $a, ManualTrecho $b) => [$ordemDaSecao[$a->ancora], $ordemDoManual[$a->id] ?? 0]
            <=> [$ordemDaSecao[$b->ancora], $ordemDoManual[$b->id] ?? 0]);

        return $resultado;
    }
}
