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

    public function render(string $html, array $dados): string
    {
        $sanitized = $this->sanitize($html);

        return preg_replace_callback('/{{\s*([a-zA-Z0-9_.]+)\s*}}/', function ($matches) use ($dados) {
            $path = $matches[1] ?? '';
            $value = data_get($dados, $path);
            if ($value === null) {
                return '';
            }
            return is_scalar($value) ? (string) $value : '';
        }, $sanitized);
    }

    protected function sanitize(string $html): string
    {
        $allowed = '';
        foreach ($this->allowedTags as $tag) {
            $allowed .= "<{$tag}>";
        }

        return strip_tags($html, $allowed);
    }
}
