<?php

namespace App\Services\CartaOferta;

class CartaOfertaTemplateRenderer
{
    protected array $allowedTags = [
        'p',
        'br',
        'strong',
        'b',
        'em',
        'i',
        'u',
        'ul',
        'ol',
        'li',
        'table',
        'thead',
        'tbody',
        'tr',
        'td',
        'th',
        'div',
        'span',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'hr',
    ];

    /** @var list<string> */
    protected array $allowedStyleProps = [
        'text-align',
        'font-weight',
        'font-style',
        'text-decoration',
        'text-transform',
        'line-height',
        'margin',
        'margin-top',
        'margin-bottom',
    ];

    public function render(string $html, array $dados): string
    {
        $sanitized = $this->sanitize($html);

        $rendered = preg_replace_callback('/{{\s*([a-zA-Z0-9_.]+)\s*}}/', function ($matches) use ($dados) {
            $path = $matches[1] ?? '';
            $value = data_get($dados, $path);
            if ($value === null) {
                return '';
            }
            return is_scalar($value)
                ? htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                : '';
        }, $sanitized) ?? $sanitized;

        return $this->removerLinhasSalarioVazias($rendered);
    }

    /**
     * Templates customizados com "Salário: {{salario}}" não devem deixar linha vazia no PDF.
     */
    protected function removerLinhasSalarioVazias(string $html): string
    {
        $limpo = preg_replace(
            '/<p\b[^>]*>\s*Sal[aá]rio\s*:\s*<strong>\s*<\/strong>\s*<\/p>/iu',
            '',
            $html
        ) ?? $html;

        return preg_replace(
            '/<p\b[^>]*>\s*Sal[aá]rio\s*:\s*<\/p>/iu',
            '',
            $limpo
        ) ?? $limpo;
    }

    protected function sanitize(string $html): string
    {
        $allowed = '';
        foreach ($this->allowedTags as $tag) {
            $allowed .= "<{$tag}>";
        }

        // Preserva style seguro (ex.: text-align) antes do strip_tags remover atributos
        $placeholders = [];
        $withMarkers = preg_replace_callback(
            '/\sstyle=("|\')(.*?)\1/i',
            function ($m) use (&$placeholders) {
                $safe = $this->filtrarEstilosSeguros($m[2] ?? '');
                if ($safe === '') {
                    return '';
                }
                $token = '___STYLE_' . count($placeholders) . '___';
                $placeholders[$token] = ' style="' . $safe . '"';

                return ' ' . $token;
            },
            $html
        ) ?? $html;

        $stripped = strip_tags($withMarkers, $allowed);

        return str_replace(array_keys($placeholders), array_values($placeholders), $stripped);
    }

    protected function filtrarEstilosSeguros(string $style): string
    {
        $parts = [];
        foreach (explode(';', $style) as $decl) {
            $decl = trim($decl);
            if ($decl === '' || !str_contains($decl, ':')) {
                continue;
            }
            [$prop, $value] = array_map('trim', explode(':', $decl, 2));
            $prop = strtolower($prop);
            if (!in_array($prop, $this->allowedStyleProps, true)) {
                continue;
            }
            // Bloqueia url()/expression e caracteres perigosos
            if (preg_match('/url\s*\(|expression\s*\(|javascript:/i', $value)) {
                continue;
            }
            if (!preg_match('/^[a-z0-9.\s\-%]+$/i', $value)) {
                continue;
            }
            $parts[] = $prop . ': ' . strtolower($value);
        }

        return implode('; ', $parts);
    }
}
