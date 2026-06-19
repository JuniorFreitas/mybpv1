<?php

namespace App\Support\IntegracaoSpa;

use Illuminate\Support\Str;

final class VagaSpaSlug
{
    public static function fromTitulo(?string $titulo): string
    {
        $slug = Str::slug((string) $titulo);

        return $slug !== '' ? $slug : 'vaga';
    }
}
