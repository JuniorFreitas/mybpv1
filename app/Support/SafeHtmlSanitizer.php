<?php

namespace App\Support;

/**
 * Sanitiza HTML de rich-text: mantém tags de formatação e remove atributos
 * perigosos (event handlers, javascript: URLs).
 */
final class SafeHtmlSanitizer
{
    private const DEFAULT_ALLOWED = '<p><a><strong><i><ul><li><ol><table><tbody><tr><td><br><em><b><u>';

    public static function sanitize(?string $html, string $allowedTags = self::DEFAULT_ALLOWED): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $clean = strip_tags($html, $allowedTags);

        // Remove event handlers e atributos perigosos, preservando href http(s)/mailto e target/rel seguros.
        $clean = preg_replace_callback(
            '/<([a-z0-9]+)(\s[^>]*)?>/i',
            static function (array $matches): string {
                $tag = strtolower($matches[1]);
                $attrs = $matches[2] ?? '';
                if ($attrs === '') {
                    return '<' . $tag . '>';
                }

                if ($tag === 'a') {
                    $href = null;
                    if (preg_match('/\shref\s*=\s*(["\'])(.*?)\1/i', $attrs, $hrefMatch)) {
                        $candidate = trim($hrefMatch[2]);
                        if (preg_match('#^(https?:|mailto:)#i', $candidate)) {
                            $href = htmlspecialchars($candidate, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        }
                    }
                    if ($href === null) {
                        return '<' . $tag . '>';
                    }

                    return '<' . $tag . ' href="' . $href . '">';
                }

                return '<' . $tag . '>';
            },
            $clean
        );

        return $clean ?? '';
    }
}
