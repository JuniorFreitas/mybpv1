<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

final class DocumentoPreadmissaoDescricaoSanitizer
{
    /** @var array<string, true> */
    private const TAGS_PERMITIDAS = [
        'p' => true,
        'br' => true,
        'strong' => true,
        'b' => true,
        'em' => true,
        'i' => true,
        'u' => true,
        'ul' => true,
        'ol' => true,
        'li' => true,
        'a' => true,
    ];

    /** @var array<string, true> */
    private const TAGS_REMOVIDAS_COM_CONTEUDO = [
        'script' => true,
        'style' => true,
        'iframe' => true,
        'object' => true,
        'embed' => true,
        'svg' => true,
        'math' => true,
        'form' => true,
        'input' => true,
        'button' => true,
        'textarea' => true,
        'select' => true,
        'option' => true,
        'link' => true,
        'meta' => true,
        'base' => true,
    ];

    public static function sanitize(mixed $html): ?string
    {
        if ($html === null || trim((string) $html) === '') {
            return null;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $carregado = $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="_preadm_root">' . (string) $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $raiz = $dom->getElementById('_preadm_root');
        if (!$carregado || !$raiz instanceof DOMElement) {
            $texto = trim(strip_tags((string) $html));

            return $texto === '' ? null : htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $xpath = new DOMXPath($dom);
        self::removerElementosPerigosos($xpath);
        self::removerTagsNaoPermitidas($xpath, $raiz);
        self::sanitizarAtributos($xpath, $raiz);

        $resultado = '';
        foreach (iterator_to_array($raiz->childNodes) as $filho) {
            $resultado .= $dom->saveHTML($filho);
        }

        $resultado = trim($resultado);

        return $resultado === '' ? null : $resultado;
    }

    private static function removerElementosPerigosos(DOMXPath $xpath): void
    {
        foreach (array_keys(self::TAGS_REMOVIDAS_COM_CONTEUDO) as $tag) {
            foreach (iterator_to_array($xpath->query('//' . $tag)) as $elemento) {
                $elemento->parentNode?->removeChild($elemento);
            }
        }
    }

    private static function removerTagsNaoPermitidas(DOMXPath $xpath, DOMElement $raiz): void
    {
        while (true) {
            $elemento = null;
            foreach (iterator_to_array($xpath->query('//*')) as $candidato) {
                if ($candidato !== $raiz && !isset(self::TAGS_PERMITIDAS[strtolower($candidato->tagName)])) {
                    $elemento = $candidato;
                    break;
                }
            }

            if (!$elemento instanceof DOMElement || !$elemento->parentNode) {
                return;
            }

            while ($elemento->firstChild !== null) {
                $elemento->parentNode->insertBefore($elemento->firstChild, $elemento);
            }
            $elemento->parentNode->removeChild($elemento);
        }
    }

    private static function sanitizarAtributos(DOMXPath $xpath, DOMElement $raiz): void
    {
        foreach (iterator_to_array($xpath->query('//*')) as $elemento) {
            if (!$elemento instanceof DOMElement || $elemento === $raiz) {
                continue;
            }

            $href = strtolower($elemento->tagName) === 'a' ? trim($elemento->getAttribute('href')) : '';
            while ($elemento->hasAttributes()) {
                $elemento->removeAttribute($elemento->attributes->item(0)->name);
            }

            if ($href !== '' && self::hrefSeguro($href)) {
                $elemento->setAttribute('href', $href);
            }
        }
    }

    private static function hrefSeguro(string $href): bool
    {
        $decodificado = html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalizado = preg_replace('/[\x00-\x20\x7F]+/u', '', $decodificado) ?? '';

        if (!preg_match('/^([a-z][a-z0-9+.-]*):/i', $normalizado, $matches)) {
            return true;
        }

        return in_array(strtolower($matches[1]), ['http', 'https', 'mailto'], true);
    }
}
