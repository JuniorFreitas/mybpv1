<?php

namespace Tests\Feature;

use Tests\Support\IntegracaoSpaV2Schema;
use Tests\TestCase;

class IntegracaoSpaMediaTest extends TestCase
{
    use IntegracaoSpaV2Schema;

    public function testLogotipoApelidoInexistenteRetorna404(): void
    {
        $this->garantirSchemaIntegracaoSpaV2();

        $this->get('/api/v2/integracao/media/empresa-que-nao-existe-xyz/logotipo')
            ->assertStatus(404);
    }
}
