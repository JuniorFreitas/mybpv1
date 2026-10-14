<?php

namespace App\Domain\Ajuda\Services;

use Illuminate\Support\Str;

/**
 * Transforma texto em termos de busca: minúsculas, sem acentos, sem palavras
 * vazias e reduzidos ao radical. Pergunta e manual passam pelo mesmo processo.
 */
final class TokenizadorManual
{
    private const RADICAL_MINIMO = 4;

    /** Do mais longo para o mais curto: o primeiro que couber é removido. */
    private const SUFIXOS = [
        'amento', 'imento', 'acao', 'ador', 'ante', 'ando', 'endo', 'indo',
        'ada', 'ado', 'ida', 'ido', 'ar', 'er', 'ir', 'a', 'e', 'o',
    ];

    private const STOPWORDS = [
        'a', 'ao', 'aos', 'as', 'com', 'como', 'da', 'das', 'de', 'do', 'dos', 'e', 'ela', 'ele', 'em', 'entre',
        'essa', 'esse', 'esta', 'este', 'eu', 'faco', 'fazer', 'fica', 'foi', 'ha', 'isso', 'isto', 'ja', 'la',
        'mais', 'mas', 'me', 'meu', 'minha', 'na', 'nas', 'nao', 'no', 'nos', 'o', 'onde', 'os', 'ou', 'para',
        'pela', 'pelo', 'pode', 'por', 'posso', 'pra', 'qual', 'quais', 'quando', 'que', 'quero', 'se', 'sem',
        'ser', 'seu', 'sua', 'sao', 'sobre', 'tem', 'tenho', 'um', 'uma', 'voce',
    ];

    /**
     * @return array<int, string>
     */
    public function tokenizar(string $texto): array
    {
        $normalizado = Str::lower(Str::ascii($texto));
        $palavras = preg_split('/[^a-z0-9]+/', $normalizado, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $tokens = [];
        foreach ($palavras as $palavra) {
            if (strlen($palavra) < 2 || in_array($palavra, self::STOPWORDS, true)) {
                continue;
            }
            $tokens[] = $this->radical($palavra);
        }

        return $tokens;
    }

    /**
     * Redução leve ao radical, para a pergunta encontrar o termo do manual mesmo em outra
     * flexão: "chamados" → "chamado", "importar" → "importação", "cadastrar" → "cadastro".
     */
    private function radical(string $palavra): string
    {
        if (strlen($palavra) <= 4) {
            return $palavra;
        }

        $palavra = $this->singular($palavra);

        foreach (self::SUFIXOS as $sufixo) {
            if (str_ends_with($palavra, $sufixo) && strlen($palavra) - strlen($sufixo) >= self::RADICAL_MINIMO) {
                return substr($palavra, 0, -strlen($sufixo));
            }
        }

        return $palavra;
    }

    private function singular(string $palavra): string
    {
        foreach (['oes' => 'ao', 'aes' => 'ao', 'ais' => 'al', 'eis' => 'el', 'ns' => 'm'] as $sufixo => $troca) {
            if (str_ends_with($palavra, $sufixo)) {
                return substr($palavra, 0, -strlen($sufixo)).$troca;
            }
        }

        return str_ends_with($palavra, 's') ? substr($palavra, 0, -1) : $palavra;
    }
}
