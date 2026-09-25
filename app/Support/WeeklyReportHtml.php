<?php

namespace App\Support;

class WeeklyReportHtml
{
    private const ALLOWED_TAGS = '<p><br><br/><strong><b><em><i><u><ul><ol><li><a><span><img>';

    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $mentions = [];
        $html = preg_replace_callback(
            '/<span\b[^>]*\bwr-mention\b[^>]*>.*?<\/span>/is',
            static function (array $matches) use (&$mentions) {
                $raw = $matches[0];
                $userId = 0;
                $nome = '';

                if (preg_match('/data-user-id\s*=\s*("|\'|)(\d+)\1/i', $raw, $idMatch)) {
                    $userId = (int) $idMatch[2];
                }
                if (preg_match('/data-nome\s*=\s*("|\')([^"\']*)\1/i', $raw, $nomeMatch)) {
                    $nome = html_entity_decode($nomeMatch[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
                if ($nome === '' && preg_match('/>([^<]*)</', $raw, $textMatch)) {
                    $nome = ltrim(html_entity_decode($textMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), '@');
                }

                $nome = trim(preg_replace('/\s+/u', ' ', $nome) ?? $nome);
                if ($userId < 1 || $nome === '') {
                    return htmlspecialchars('@' . ($nome !== '' ? $nome : 'membro'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }

                $safeNome = htmlspecialchars($nome, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $token = '{{WRMENTION' . count($mentions) . '}}';
                $mentions[$token] = '<span class="wr-mention" contenteditable="false" data-user-id="'
                    . $userId . '" data-nome="' . $safeNome . '">@' . $safeNome . '</span>';

                return $token;
            },
            $html
        ) ?? $html;

        $clean = strip_tags($html, self::ALLOWED_TAGS);
        $clean = preg_replace('/\son\w+\s*=\s*("|\')[^"\']*\1/i', '', $clean) ?? $clean;
        $clean = preg_replace_callback(
            '/<a\b([^>]*)>/i',
            static function (array $matches) {
                $attrs = $matches[1] ?? '';
                $href = '';
                if (preg_match('/href\s*=\s*("|\')([^"\']+)\1/i', $attrs, $hrefMatch)) {
                    $href = trim($hrefMatch[2]);
                }
                if ($href === '' || preg_match('/^\s*javascript:/i', $href)) {
                    return '<a>';
                }

                return '<a href="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">';
            },
            $clean
        ) ?? $clean;

        $clean = preg_replace_callback(
            '/<img\b([^>]*)>/i',
            static function (array $matches) {
                $attrs = $matches[1] ?? '';
                $src = '';
                if (preg_match('/src\s*=\s*("|\')([^"\']+)\1/i', $attrs, $srcMatch)) {
                    $src = trim($srcMatch[2]);
                }
                if (!self::isAllowedImageSrc($src)) {
                    return '';
                }

                $alt = '';
                if (preg_match('/alt\s*=\s*("|\')([^"\']*)\1/i', $attrs, $altMatch)) {
                    $alt = $altMatch[2];
                }

                return '<img src="' . htmlspecialchars($src, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    . '" alt="' . htmlspecialchars($alt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    . '" class="img-fluid">';
            },
            $clean
        ) ?? $clean;

        // Remover spans genéricos (atributos já foram stripados); menções voltam pelos tokens
        $clean = preg_replace('/<\/?span\b[^>]*>/i', '', $clean) ?? $clean;

        foreach ($mentions as $token => $span) {
            $clean = str_replace($token, $span, $clean);
        }

        return trim($clean);
    }

    /**
     * Extrai user ids de spans.wr-mention no HTML (já sanitizado ou bruto).
     *
     * @return list<int>
     */
    public static function extractMentionUserIds(?string $html): array
    {
        $html = (string) $html;
        if ($html === '' || !preg_match_all('/<span\b[^>]*\bwr-mention\b[^>]*>/i', $html, $spans)) {
            return [];
        }

        $ids = [];
        foreach ($spans[0] as $openTag) {
            if (preg_match('/data-user-id\s*=\s*("|\'|)(\d+)\1/i', $openTag, $idMatch)) {
                $id = (int) $idMatch[2];
                if ($id > 0) {
                    $ids[$id] = $id;
                }
            }
        }

        return array_values($ids);
    }

    public static function isEmpty(?string $html): bool
    {
        if (preg_match('/<img\b/i', (string) $html)) {
            return false;
        }

        $text = trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace('/\x{00A0}/u', ' ', $text) ?? $text;

        return trim($text) === '';
    }

    private static function isAllowedImageSrc(string $src): bool
    {
        if ($src === '' || preg_match('/^\s*(javascript|vbscript|data):/i', $src)) {
            return false;
        }

        // Caminhos internos do weekly-report (anexo do card)
        if (preg_match('#(^|/)g/weekly-report/.+/anexo/#i', $src) || preg_match('#/weekly-report/.+/anexo/#i', $src)) {
            return true;
        }

        // URL absoluta do próprio app
        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl !== '' && str_starts_with($src, $appUrl . '/')) {
            return str_contains($src, '/weekly-report/') && str_contains($src, '/anexo/');
        }

        // Relativo começando com /
        if (str_starts_with($src, '/')) {
            return str_contains($src, '/weekly-report/') && str_contains($src, '/anexo/');
        }

        return false;
    }
}
