<?php

namespace App\Infrastructure\Ajuda;

use Illuminate\Support\Facades\Cache;

/**
 * Recorta de manual.html uma única seção (um <h1>/<h2> até o próximo título),
 * com o estilo do manual, para ser exibida isolada. O manual completo nunca é
 * entregue ao navegador.
 */
final class ManualSecaoHtml
{
    private const MANUAL_HTML = 'docs/manual-completo/manual.html';

    private const CACHE_TTL_SECONDS = 86400;

    private const ANCORA_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /** Ajustes de tela: sem o cabeçalho de página e sem quebra antes do título. */
    private const ESTILO_SECAO = 'body{max-width:none;margin:0;padding:16px 20px;}h1,h2{break-before:auto;margin-top:0;}img{max-width:100%;height:auto;}';

    public function render(string $ancora, string $baseHref): ?string
    {
        if (! preg_match(self::ANCORA_PATTERN, $ancora)) {
            return null;
        }

        $path = base_path(self::MANUAL_HTML);
        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }

        // A versão do arquivo na chave invalida o cache quando o manual é regenerado.
        $versao = substr(sha1($path.'|'.filemtime($path).'|'.filesize($path)), 0, 12);

        $partes = Cache::remember(
            'ajuda:manual:secao:'.$versao.':'.$ancora,
            self::CACHE_TTL_SECONDS,
            fn () => $this->recortar((string) file_get_contents($path), $ancora) ?? false,
        );

        if (! is_array($partes)) {
            return null;
        }

        return '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8">'
            .'<base href="'.e($baseHref).'">'
            .'<style>'.$partes['estilo'].self::ESTILO_SECAO.'</style>'
            .'</head><body>'.$partes['corpo'].'</body></html>';
    }

    /**
     * @return array{estilo: string, corpo: string}|null
     */
    private function recortar(string $html, string $ancora): ?array
    {
        if (! preg_match('/<h[12] id="'.preg_quote($ancora, '/').'"[^>]*>/', $html, $inicio, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $posicao = $inicio[0][1];
        $resto = substr($html, $posicao + strlen($inicio[0][0]));

        // A seção termina no próximo título de capítulo/seção, na contracapa ou no fim da página.
        $tamanho = preg_match('/<h[12][\s>]|<section\b|<\/td><\/tr><\/tbody>/', $resto, $fim, PREG_OFFSET_CAPTURE)
            ? $fim[0][1]
            : strlen($resto);

        $estilo = preg_match('/<style>(.*?)<\/style>/s', $html, $style) ? $style[1] : '';
        // Regras de impressão/A4 não fazem sentido na tela.
        $estilo = preg_replace('/@page[^{]*\{(?:[^{}]++|\{[^{}]*+\})*+\}/s', '', $estilo) ?? $estilo;

        return [
            'estilo' => $estilo,
            'corpo' => $inicio[0][0].substr($resto, 0, $tamanho),
        ];
    }
}
