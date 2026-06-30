<?php

namespace App\Domain\Whatsapp\Services;

class WhatsappTemplateRenderer
{
    private const FALLBACK = 'Não informado';

    public function render(string $template, array $contexto): string
    {
        return preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',
            function (array $matches) use ($contexto) {
                $chave = $matches[1];

                if (!array_key_exists($chave, $contexto)) {
                    return self::FALLBACK;
                }

                $valor = $contexto[$chave];

                if ($valor === null) {
                    return self::FALLBACK;
                }

                return (string) $valor;
            },
            $template
        );
    }
}
