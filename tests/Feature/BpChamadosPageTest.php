<?php

namespace Tests\Feature;

use App\Http\Controllers\BpChamadosController;
use App\Services\BpChamados\BpChamadosWidgetTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Tests\TestCase;

class BpChamadosPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.bp_chamados.enabled' => true,
            'services.bp_chamados.api_base_url' => 'http://localhost:8100',
            'services.bp_chamados.application_id' => '01TESTAPPIDPUBLICULIDXX',
            'services.bp_chamados.widget_secret' => 'ws_test_secret_page',
            'services.bp_chamados.token_ttl' => 900,
        ]);
    }

    public function testIndexRetornaViewQuandoHabilitado(): void
    {
        $response = (new BpChamadosController())->index(
            app(BpChamadosWidgetTokenService::class)
        );

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('g.bp-chamados.index', $response->name());
        $this->assertSame('http://localhost:8100', $response->getData()['apiBaseUrl']);
        $this->assertSame('01TESTAPPIDPUBLICULIDXX', $response->getData()['applicationId']);
        $this->assertNotEmpty($response->getData()['tokenUrl']);
    }

    public function testIndexRedirecionaQuandoDesabilitado(): void
    {
        config(['services.bp_chamados.enabled' => false]);

        $response = (new BpChamadosController())->index(
            app(BpChamadosWidgetTokenService::class)
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isRedirect(route('g.dashboard')));
    }
}
