<?php

namespace App\Support;

/**
 * Gera SVG de radar (0–5) para o PDF de desempenho, alinhado ao Chart.js do front (Radar.vue: min 0, max 5).
 */
final class AvaliacaoDesempenhoPdfRadarSvg
{
    private const SCALE_MIN = 0.0;

    private const SCALE_MAX = 5.0;

    /**
     * @param  array<string, mixed>  $dados  Retorno de avaliarFinal()
     * @return list<array{name: string, svg: string}>
     */
    public static function chartsFromDados(array $dados): array
    {
        $resultTopico = collect($dados['result_topico'] ?? []);
        $out = [];
        foreach (collect($dados['resultChart'] ?? []) as $chart) {
            $name = trim((string) ($chart['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $rows = $resultTopico->where('topico_pai', $name)->values();
            if ($rows->isEmpty()) {
                continue;
            }
            $labels = $rows->map(fn ($r) => self::truncateLabel((string) ($r['subtopico'] ?? ''), 20))->all();
            $values = $rows->map(fn ($r) => (float) ($r['media_redonda'] ?? $r['media'] ?? 0))->all();
            $render = self::radarOrFallbackWithDimensions($labels, $values);
            $out[] = [
                'name' => $name,
                'svg' => $render['svg'],
                'img_w' => $render['w'],
                'img_h' => $render['h'],
            ];
        }

        return $out;
    }

    /**
     * @param  list<string>  $labels
     * @param  list<float>  $values
     * @return array{svg: string, w: int, h: int}
     */
    public static function radarOrFallbackWithDimensions(array $labels, array $values): array
    {
        $n = count($values);
        if ($n === 0) {
            return ['svg' => '', 'w' => 0, 'h' => 0];
        }
        if ($n < 3) {
            return ['svg' => self::horizontalBarsSvg($labels, $values, 320, 140), 'w' => 320, 'h' => 140];
        }

        return ['svg' => self::radarSvg($labels, $values, 340, 300), 'w' => 340, 'h' => 300];
    }

    private static function truncateLabel(string $text, int $maxChars): string
    {
        $text = trim($text);
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($text, 'UTF-8') <= $maxChars) {
                return $text;
            }

            return rtrim(mb_substr($text, 0, $maxChars - 1, 'UTF-8')) . '…';
        }
        if (strlen($text) <= $maxChars) {
            return $text;
        }

        return substr($text, 0, $maxChars - 1) . '…';
    }

    /**
     * @param  list<string>  $labels
     * @param  list<float>  $values
     */
    private static function radarSvg(array $labels, array $values, int $width, int $height): string
    {
        $n = count($values);
        $cx = $width / 2;
        $cy = $height / 2 + 8;
        $maxR = min($width, $height) / 2 - 52;

        $grid = '#d5dee8';
        $axis = '#94a3b8';
        $fill = 'rgba(255, 165, 0, 0.28)';
        $stroke = '#ff8c00';
        $pointFill = '#ffffff';
        $pointStroke = '#ff8c00';

        $parts = [];
        $parts[] = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">',
            $width,
            $height,
            $width,
            $height
        );
        $parts[] = '<rect width="100%" height="100%" fill="#f8fafc" stroke="#ecf0f1" stroke-width="1"/>';

        for ($step = 1; $step <= 5; $step++) {
            $r = $maxR * ($step / 5);
            $pts = [];
            for ($i = 0; $i < $n; $i++) {
                $a = -M_PI / 2 + ($i * 2 * M_PI / $n);
                $pts[] = sprintf('%.2f,%.2f', $cx + cos($a) * $r, $cy + sin($a) * $r);
            }
            $parts[] = sprintf(
                '<polygon fill="none" stroke="%s" stroke-width="0.9" points="%s"/>',
                htmlspecialchars($grid, ENT_QUOTES | ENT_XML1, 'UTF-8'),
                implode(' ', $pts)
            );
        }

        for ($i = 0; $i < $n; $i++) {
            $a = -M_PI / 2 + ($i * 2 * M_PI / $n);
            $x2 = $cx + cos($a) * $maxR;
            $y2 = $cy + sin($a) * $maxR;
            $parts[] = sprintf(
                '<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="%s" stroke-width="1"/>',
                $cx,
                $cy,
                $x2,
                $y2,
                htmlspecialchars($axis, ENT_QUOTES | ENT_XML1, 'UTF-8')
            );
        }

        $polyPts = [];
        $circles = [];
        for ($i = 0; $i < $n; $i++) {
            $v = self::clampScale($values[$i]);
            $r = (($v - self::SCALE_MIN) / (self::SCALE_MAX - self::SCALE_MIN)) * $maxR;
            $a = -M_PI / 2 + ($i * 2 * M_PI / $n);
            $px = $cx + cos($a) * $r;
            $py = $cy + sin($a) * $r;
            $polyPts[] = sprintf('%.2f,%.2f', $px, $py);
            $circles[] = sprintf(
                '<circle cx="%.2f" cy="%.2f" r="4" fill="%s" stroke="%s" stroke-width="1.8"/>',
                $px,
                $py,
                htmlspecialchars($pointFill, ENT_QUOTES | ENT_XML1, 'UTF-8'),
                htmlspecialchars($pointStroke, ENT_QUOTES | ENT_XML1, 'UTF-8')
            );
        }

        $parts[] = sprintf(
            '<polygon fill="%s" stroke="%s" stroke-width="2.2" points="%s"/>',
            htmlspecialchars($fill, ENT_QUOTES | ENT_XML1, 'UTF-8'),
            htmlspecialchars($stroke, ENT_QUOTES | ENT_XML1, 'UTF-8'),
            implode(' ', $polyPts)
        );
        $parts = array_merge($parts, $circles);

        for ($i = 0; $i < $n; $i++) {
            $a = -M_PI / 2 + ($i * 2 * M_PI / $n);
            $lr = $maxR + 22;
            $lx = $cx + cos($a) * $lr;
            $ly = $cy + sin($a) * $lr;
            $label = htmlspecialchars($labels[$i] ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');
            $anchor = 'middle';
            $dx = 0;
            if (cos($a) < -0.25) {
                $anchor = 'end';
                $dx = -4;
            } elseif (cos($a) > 0.25) {
                $anchor = 'start';
                $dx = 4;
            }
            $parts[] = sprintf(
                '<text x="%.2f" y="%.2f" text-anchor="%s" font-size="7" fill="#2c3e50" font-family="DejaVu Sans, sans-serif">%s</text>',
                $lx + $dx,
                $ly + 3,
                $anchor,
                $label
            );
        }

        $parts[] = sprintf(
            '<text x="%.2f" y="14" text-anchor="middle" font-size="8" fill="#64748b" font-family="DejaVu Sans, sans-serif">Escala 0 a 5</text>',
            $cx
        );

        $parts[] = '</svg>';

        return implode('', $parts);
    }

    /**
     * @param  list<string>  $labels
     * @param  list<float>  $values
     */
    private static function horizontalBarsSvg(array $labels, array $values, int $width, int $height): string
    {
        $rowH = min(28, (int) floor(($height - 30) / max(1, count($values))));
        $barMaxW = $width - 130;
        $parts = [];
        $parts[] = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">',
            $width,
            $height,
            $width,
            $height
        );
        $parts[] = '<rect width="100%" height="100%" fill="#f8fafc"/>';
        $y = 18;
        foreach ($values as $i => $v) {
            $v = self::clampScale($v);
            $w = ($v / self::SCALE_MAX) * $barMaxW;
            $lab = htmlspecialchars(self::truncateLabel($labels[$i] ?? '', 28), ENT_QUOTES | ENT_XML1, 'UTF-8');
            $parts[] = sprintf(
                '<text x="6" y="%.1f" font-size="7" fill="#2c3e50" font-family="DejaVu Sans, sans-serif">%s</text>',
                $y + $rowH / 2 + 2,
                $lab
            );
            $parts[] = sprintf(
                '<rect x="118" y="%.1f" width="%.1f" height="%.0f" rx="3" fill="#ff8c00" stroke="#e67e00" stroke-width="0.6"/>',
                $y,
                max(2, $w),
                $rowH - 6
            );
            $parts[] = sprintf(
                '<text x="%.1f" y="%.1f" font-size="7" fill="#c0392b" font-family="DejaVu Sans, sans-serif" font-weight="bold">%s</text>',
                118 + $w + 4,
                $y + $rowH / 2 + 2,
                htmlspecialchars(number_format($v, 1, '.', ''), ENT_QUOTES | ENT_XML1, 'UTF-8')
            );
            $y += $rowH;
        }
        $parts[] = '</svg>';

        return implode('', $parts);
    }

    private static function clampScale(float $v): float
    {
        return min(self::SCALE_MAX, max(self::SCALE_MIN, $v));
    }
}
