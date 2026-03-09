<?php

namespace Tests\Unit;

use App\Http\Controllers\AssinaturaPublicaController;
use App\Models\DocumentoAssinaturaEvento;
use App\Models\DocumentoAssinaturaSignatario;
use App\Models\DocumentoParaAssinatura;
use App\Services\AssinaturaDigital\AssinaturaDigitalService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class AssinaturaPublicaControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testIndexRegistraEventoVisualizadoComEvidencias(): void
    {
        $doc = new FakeDocumentoParaAssinaturaPublica();
        $doc->id = 55;
        $doc->status = DocumentoParaAssinatura::STATUS_EM_ASSINATURA;

        $signatario = new FakeDocumentoAssinaturaSignatarioPublica();
        $signatario->id = 77;
        $signatario->status = DocumentoAssinaturaSignatario::STATUS_PENDENTE;
        $signatario->setRelation('documentoParaAssinatura', $doc);

        $service = Mockery::mock(AssinaturaDigitalService::class);
        $service->shouldReceive('validarTokenParaEmpresa')->once()->andReturn($signatario);
        $service->shouldReceive('registrarEvento')
            ->once()
            ->withArgs(function ($docId, $evento, $payload) use ($signatario) {
                $this->assertSame(55, $docId);
                $this->assertSame(DocumentoAssinaturaEvento::EVENTO_VISUALIZADO, $evento);
                $this->assertSame($signatario->id, $payload['signatario_id']);
                $this->assertSame('189.10.10.10', $payload['ip']);
                $this->assertSame('TestAgent', $payload['user_agent']);
                $this->assertArrayHasKey('data_utc', $payload);
                return true;
            })
            ->andReturn(Mockery::mock(DocumentoAssinaturaEvento::class));

        $controller = new AssinaturaPublicaController($service);

        $request = Request::create('/bpse/assinatura/abc', 'GET', [], [], [], [
            'REMOTE_ADDR' => '189.10.10.10',
            'HTTP_USER_AGENT' => 'TestAgent',
        ]);
        $session = app('session')->driver();
        $session->start();
        $request->setLaravelSession($session);
        $request->session()->put('assinatura_publica_verificado.token_teste', [
            'signatario_id' => 77,
            'verified_at' => now()->toDateTimeString(),
        ]);

        $response = $controller->index($request, 'bpse', 'token_teste');

        $this->assertSame('assinatura.assinar', $response->name());
    }
}

class FakeDocumentoAssinaturaSignatarioPublica extends DocumentoAssinaturaSignatario
{
}

class FakeDocumentoParaAssinaturaPublica extends DocumentoParaAssinatura
{
}
