<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * HTML seguro para impressão do PDI (plano de ação): negrito, itálico, sublinhado
 * e font-size / font-family limitados — sem scripts nem atributos perigosos.
 */
final class AvaliacaoPlanoAcaoImpressaoHtmlSanitizer
{
    /** @var array<string, true> */
    private const TAG_BLOCKLIST = [
        'script' => true,
        'iframe' => true,
        'object' => true,
        'embed' => true,
        'link' => true,
        'meta' => true,
        'style' => true,
        'form' => true,
        'input' => true,
        'button' => true,
        'textarea' => true,
        'select' => true,
        'option' => true,
        'svg' => true,
        'math' => true,
        'base' => true,
        'img' => true,
        'video' => true,
        'audio' => true,
    ];

    private const ALLOWED_XPATH = 'self::p or self::br or self::b or self::strong or self::i or self::em or self::u or self::span';

    public static function sanitize(?string $html): string
    {
        if ($html === null) {
            return '';
        }
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        if (! preg_match('/<\s*[a-zA-Z]/', $html)) {
            return '<p>' . nl2br(htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) . '</p>';
        }

        $wrapped = '<div id="_sroot">' . $html . '</div>';
        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?>' . $wrapped,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        if (! $loaded) {
            return '<p>' . htmlspecialchars(strip_tags($html), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        }

        $root = $dom->getElementById('_sroot');
        if (! $root instanceof DOMElement) {
            return '<p>' . htmlspecialchars(strip_tags($html), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        }

        self::removeBlocklistedTags($dom);
        $xp = new DOMXPath($dom);

        while (true) {
            $node = $xp->query('//*[not(' . self::ALLOWED_XPATH . ") and not(@id='_sroot')]")->item(0);
            if (! $node instanceof DOMElement) {
                break;
            }
            self::unwrapElement($node);
        }

        foreach (iterator_to_array($xp->query('//span')) as $span) {
            if ($span instanceof DOMElement) {
                self::sanitizeSpan($span);
            }
        }

        foreach (iterator_to_array($xp->query('//p|//b|//strong|//i|//em|//u')) as $el) {
            if ($el instanceof DOMElement) {
                self::stripAllAttributes($el);
            }
        }

        foreach (iterator_to_array($xp->query('//br')) as $br) {
            if ($br instanceof DOMElement) {
                self::stripAllAttributes($br);
            }
        }

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out === '' ? '<p></p>' : $out;
    }

    private static function removeBlocklistedTags(DOMDocument $dom): void
    {
        $xp = new DOMXPath($dom);
        foreach (array_keys(self::TAG_BLOCKLIST) as $tag) {
            $nodes = $xp->query('//' . $tag);
            if (! $nodes) {
                continue;
            }
            foreach (iterator_to_array($nodes) as $node) {
                if ($node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        }
    }

    private static function unwrapElement(DOMElement $node): void
    {
        $parent = $node->parentNode;
        if ($parent === null) {
            return;
        }
        while ($node->firstChild !== null) {
            $parent->insertBefore($node->firstChild, $node);
        }
        $parent->removeChild($node);
    }

    private static function stripAllAttributes(DOMElement $el): void
    {
        while ($el->hasAttributes()) {
            $el->removeAttribute($el->attributes->item(0)->name);
        }
    }

    private static function sanitizeSpan(DOMElement $span): void
    {
        $rawStyle = $span->getAttribute('style');
        self::stripAllAttributes($span);
        $clean = self::sanitizeStyleDeclaration($rawStyle);
        if ($clean !== '') {
            $span->setAttribute('style', $clean);
        }
    }

    private static function sanitizeStyleDeclaration(string $style): string
    {
        $allowedSizes = ['12pt', '14pt', '18pt', '24pt', '36pt'];
        $out = [];

        foreach (array_filter(array_map('trim', explode(';', $style))) as $decl) {
            if (preg_match('/^font-size:\s*(\S+)\s*$/i', $decl, $m)) {
                $size = strtolower($m[1]);
                if (in_array($size, $allowedSizes, true)) {
                    $out['font-size'] = 'font-size: ' . $size;
                }
                continue;
            }
            if (preg_match('/^font-family:\s*(.+)$/i', $decl, $m)) {
                $fam = self::normalizeFontFamily($m[1]);
                if ($fam !== '') {
                    $out['font-family'] = 'font-family: ' . $fam;
                }
            }
        }

        return implode('; ', array_values($out));
    }

    private static function normalizeFontFamily(string $value): string
    {
        $v = trim($value);
        if ($v === '') {
            return '';
        }
        $first = trim(explode(',', $v)[0]);
        $first = trim($first, " \t\n\r\0\x0B'\"");
        if (strcasecmp($first, 'Arial') === 0) {
            return 'Arial, Helvetica, sans-serif';
        }

        return '';
    }
}
