<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class BpChamadosWidgetTokenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.bp_chamados.enabled' => true,
            'services.bp_chamados.api_base_url' => 'http://localhost:8100',
            'services.bp_chamados.application_id' => '01TESTAPPIDPUBLICULIDXX',
            'services.bp_chamados.widget_secret' => 'ws_test_secret_feature',
            'services.bp_chamados.token_ttl' => 600,
        ]);
    }

    public function testRetornaTokenParaUsuarioAutenticado(): void
    {
        $user = new User();
        $user->forceFill([
            'id' => 7,
            'nome' => 'João Widget',
            'login' => 'joao@mybp.test',
            'tipo' => User::ADMINISTRADOR,
            'ativo' => true,
            'temp' => false,
        ]);
        $user->exists = true;
        $user->setRelation('Empresa', null);

        $this->actingAs($user);

        $response = $this->getJson(route('bp-chamados.widget-token'));

        $response->assertOk()
            ->assertJsonStructure(['token', 'expires_in']);
        $this->assertSame(600, $response->json('expires_in'));
        $this->assertNotEmpty($response->json('token'));
    }

    public function testRetorna503QuandoDesabilitado(): void
    {
        config(['services.bp_chamados.enabled' => false]);

        $user = new User();
        $user->forceFill([
            'id' => 8,
            'nome' => 'João Widget',
            'login' => 'joao@mybp.test',
            'tipo' => User::ADMINISTRADOR,
            'ativo' => true,
            'temp' => false,
        ]);
        $user->exists = true;
        $user->setRelation('Empresa', null);

        $this->actingAs($user);

        $this->getJson(route('bp-chamados.widget-token'))
            ->assertStatus(503);
    }
}
