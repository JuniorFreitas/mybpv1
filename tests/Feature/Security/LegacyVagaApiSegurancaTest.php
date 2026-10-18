<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

/**
 * POSTs legacy de currículo devem exigir apitoken (portal migrado para BFF/v2).
 */
class LegacyVagaApiSegurancaTest extends TestCase
{
    public function test_seguranca_busca_curriculo_legacy_sem_token_retorna_401_ou_403(): void
    {
        $response = $this->postJson('/api/busca-curriculo', [
            'empresa_id' => 1,
            'cpf' => '529.982.247-25',
            'nascimento' => '01/01/1990',
        ]);

        $this->assertContains($response->status(), [401, 403]);
    }

    public function test_seguranca_cadastra_curriculo_legacy_sem_token_retorna_401_ou_403(): void
    {
        $response = $this->postJson('/api/cadastra-curriculo', [
            'empresa_id' => 1,
            'cpf' => '529.982.247-25',
        ]);

        $this->assertContains($response->status(), [401, 403]);
    }
}
