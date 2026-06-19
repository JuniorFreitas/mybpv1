<?php

namespace Tests\Unit\Support;

use App\Support\IntegracaoSpa\VagaSpaSlug;
use Tests\TestCase;

class VagaSpaSlugTest extends TestCase
{
    public function testSlugNormalizaTitulo(): void
    {
        $this->assertSame('vaga-aberta-teste', VagaSpaSlug::fromTitulo('Vaga Aberta Teste'));
    }

    public function testTituloVazioRetornaVaga(): void
    {
        $this->assertSame('vaga', VagaSpaSlug::fromTitulo(''));
        $this->assertSame('vaga', VagaSpaSlug::fromTitulo(null));
    }
}
