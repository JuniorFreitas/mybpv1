<?php

namespace Tests\Unit\Domain\Whatsapp;

use App\Domain\Whatsapp\Services\WhatsappCurriculoTelefoneResolver;
use App\Models\TelefoneCurriculo;
use Tests\TestCase;

class WhatsappCurriculoTelefoneResolverTest extends TestCase
{
    private WhatsappCurriculoTelefoneResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new WhatsappCurriculoTelefoneResolver();
    }

    public function testTelefonePrincipalPermiteWhatsappQuandoTipoWhatsapp(): void
    {
        $telefone = new TelefoneCurriculo([
            'tipo' => TelefoneCurriculo::TIPO_WHATS,
            'numero' => '98999023762',
            'principal' => true,
        ]);

        $this->assertTrue($this->resolver->telefonePrincipalPermiteWhatsapp($telefone));
    }

    public function testTelefonePrincipalBloqueiaQuandoTipoCelular(): void
    {
        $telefone = new TelefoneCurriculo([
            'tipo' => TelefoneCurriculo::TIPO_CELULAR,
            'numero' => '98999023762',
            'principal' => true,
        ]);

        $this->assertFalse($this->resolver->telefonePrincipalPermiteWhatsapp($telefone));
    }
}
