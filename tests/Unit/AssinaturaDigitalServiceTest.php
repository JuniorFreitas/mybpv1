<?php

namespace Tests\Unit;

use App\Models\DocumentoAssinaturaEvento;
use App\Models\DocumentoAssinaturaSignatario;
use App\Models\DocumentoParaAssinatura;
use App\Services\AssinaturaDigital\AssinaturaDigitalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class AssinaturaDigitalServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testAssinarRetornaErroQuandoTokenInvalido(): void
    {
        /** @var AssinaturaDigitalService|Mockery\MockInterface $service */
        $service = Mockery::mock(AssinaturaDigitalService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('buscarSignatarioPorToken')->once()->andReturn(null);

        $result = $service->assinar('token_invalido', Request::create('/', 'POST'));

        $this->assertFalse($result['success']);
        $this->assertSame('Link inválido ou expirado.', $result['message']);
    }

    public function testAssinarRetornaErroQuandoDocumentoExpirado(): void
    {
        $doc = new FakeDocumentoParaAssinatura();
        $doc->id = 12;
        $doc->status = DocumentoParaAssinatura::STATUS_EM_ASSINATURA;
        $doc->data_expiracao = now()->subDay();

        $signatario = new FakeDocumentoAssinaturaSignatario();
        $signatario->id = 22;
        $signatario->status = DocumentoAssinaturaSignatario::STATUS_PENDENTE;
        $signatario->setRelation('documentoParaAssinatura', $doc);

        /** @var AssinaturaDigitalService|Mockery\MockInterface $service */
        $service = Mockery::mock(AssinaturaDigitalService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('buscarSignatarioPorToken')->once()->andReturn($signatario);

        $result = $service->assinar('token_valido', Request::create('/', 'POST'));

        $this->assertFalse($result['success']);
        $this->assertSame('O prazo para assinatura expirou.', $result['message']);
    }

    public function testAssinarRetornaErroQuandoDocumentoJaConcluido(): void
    {
        $doc = new FakeDocumentoParaAssinatura();
        $doc->id = 13;
        $doc->status = DocumentoParaAssinatura::STATUS_CONCLUIDO;

        $signatario = new FakeDocumentoAssinaturaSignatario();
        $signatario->id = 23;
        $signatario->status = DocumentoAssinaturaSignatario::STATUS_PENDENTE;
        $signatario->setRelation('documentoParaAssinatura', $doc);

        /** @var AssinaturaDigitalService|Mockery\MockInterface $service */
        $service = Mockery::mock(AssinaturaDigitalService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('buscarSignatarioPorToken')->once()->andReturn($signatario);

        $result = $service->assinar('token_valido', Request::create('/', 'POST'));

        $this->assertFalse($result['success']);
        $this->assertSame('Este documento não está mais disponível para assinatura.', $result['message']);
    }

    public function testAssinarRetornaErroQuandoDocumentoCancelado(): void
    {
        $doc = new FakeDocumentoParaAssinatura();
        $doc->id = 15;
        $doc->status = DocumentoParaAssinatura::STATUS_CANCELADO;

        $signatario = new FakeDocumentoAssinaturaSignatario();
        $signatario->id = 25;
        $signatario->status = DocumentoAssinaturaSignatario::STATUS_PENDENTE;
        $signatario->setRelation('documentoParaAssinatura', $doc);

        /** @var AssinaturaDigitalService|Mockery\MockInterface $service */
        $service = Mockery::mock(AssinaturaDigitalService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('buscarSignatarioPorToken')->once()->andReturn($signatario);

        $result = $service->assinar('token_valido', Request::create('/', 'POST'));

        $this->assertFalse($result['success']);
        $this->assertSame('Este documento não está mais disponível para assinatura.', $result['message']);
    }

    public function testAssinarRetornaErroQuandoSignatarioNaoEstaPendente(): void
    {
        $doc = new FakeDocumentoParaAssinatura();
        $doc->id = 14;
        $doc->status = DocumentoParaAssinatura::STATUS_EM_ASSINATURA;

        $signatario = new FakeDocumentoAssinaturaSignatario();
        $signatario->id = 24;
        $signatario->status = DocumentoAssinaturaSignatario::STATUS_ASSINADO;
        $signatario->setRelation('documentoParaAssinatura', $doc);

        /** @var AssinaturaDigitalService|Mockery\MockInterface $service */
        $service = Mockery::mock(AssinaturaDigitalService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('buscarSignatarioPorToken')->once()->andReturn($signatario);

        $result = $service->assinar('token_valido', Request::create('/', 'POST'));

        $this->assertFalse($result['success']);
        $this->assertSame('Este documento já foi assinado ou recusado.', $result['message']);
    }

    public function testAssinarPersisteConsentimentoEEvento(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $doc = new FakeDocumentoParaAssinatura();
        $doc->id = 10;
        $doc->status = DocumentoParaAssinatura::STATUS_EM_ASSINATURA;

        $signatario = new FakeDocumentoAssinaturaSignatario();
        $signatario->id = 20;
        $signatario->status = DocumentoAssinaturaSignatario::STATUS_PENDENTE;
        $signatario->email = 'teste@mybp.com.br';
        $signatario->nome = 'Teste Assinatura';
        $signatario->cpf = '111.444.777-35';
        $signatario->setRelation('documentoParaAssinatura', $doc);

        /** @var AssinaturaDigitalService|Mockery\MockInterface $service */
        $service = Mockery::mock(AssinaturaDigitalService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('buscarSignatarioPorToken')->once()->andReturn($signatario);
        $service->shouldReceive('obterGeolocalizacaoPorIp')->once()->andReturn(['city' => 'São Paulo']);
        $service->shouldReceive('verificarConclusao')->once()->with($doc);
        $service->shouldReceive('registrarEvento')
            ->once()
            ->withArgs(function ($docId, $evento, $payload) use ($signatario) {
                $this->assertSame(10, $docId);
                $this->assertSame(DocumentoAssinaturaEvento::EVENTO_ASSINADO, $evento);
                $this->assertSame($signatario->id, $payload['signatario_id']);
                $this->assertSame($signatario->email, $payload['email']);
                $this->assertSame($signatario->nome, $payload['nome']);
                $this->assertSame($signatario->cpf, $payload['cpf']);
                $this->assertSame('189.10.10.10', $payload['ip']);
                $this->assertSame('TestAgent', $payload['user_agent']);
                $this->assertTrue($payload['consentimento_assinatura']);
                $this->assertArrayHasKey('geolocalizacao', $payload);
                $this->assertArrayHasKey('hash_evidencia', $payload);
                return true;
            })
            ->andReturn(Mockery::mock(DocumentoAssinaturaEvento::class));

        $request = Request::create('/', 'POST', [], [], [], [
            'REMOTE_ADDR' => '189.10.10.10',
            'HTTP_USER_AGENT' => 'TestAgent',
        ]);

        $result = $service->assinar('token_valido', $request, '111.444.777-35');

        $this->assertTrue($result['success']);
        $this->assertSame(DocumentoAssinaturaSignatario::STATUS_ASSINADO, $signatario->updated['status']);
        $this->assertTrue($signatario->updated['consentimento_assinatura']);
        $this->assertArrayHasKey('hash_evidencia', $signatario->updated);
        $this->assertSame($signatario->id, $doc->updated['consentimento_ultimo_signatario_id']);
    }

    public function testRecusarRegistraEvidenciasNoEvento(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $doc = new FakeDocumentoParaAssinatura();
        $doc->id = 11;

        $signatario = new FakeDocumentoAssinaturaSignatario();
        $signatario->id = 30;
        $signatario->status = DocumentoAssinaturaSignatario::STATUS_PENDENTE;
        $signatario->email = 'teste@mybp.com.br';
        $signatario->nome = 'Teste Assinatura';
        $signatario->cpf = '111.444.777-35';
        $signatario->setRelation('documentoParaAssinatura', $doc);

        /** @var AssinaturaDigitalService|Mockery\MockInterface $service */
        $service = Mockery::mock(AssinaturaDigitalService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('buscarSignatarioPorToken')->once()->andReturn($signatario);
        $service->shouldReceive('obterGeolocalizacaoPorIp')->once()->andReturn(['city' => 'São Paulo']);
        $service->shouldReceive('registrarEvento')
            ->once()
            ->withArgs(function ($docId, $evento, $payload) use ($signatario) {
                $this->assertSame(11, $docId);
                $this->assertSame(DocumentoAssinaturaEvento::EVENTO_RECUSADO, $evento);
                $this->assertSame($signatario->email, $payload['email']);
                $this->assertSame($signatario->nome, $payload['nome']);
                $this->assertSame($signatario->cpf, $payload['cpf']);
                $this->assertSame('189.10.10.10', $payload['ip']);
                $this->assertSame('TestAgent', $payload['user_agent']);
                $this->assertArrayHasKey('data_utc', $payload);
                $this->assertArrayHasKey('geolocalizacao', $payload);
                return true;
            })
            ->andReturn(Mockery::mock(DocumentoAssinaturaEvento::class));

        $request = Request::create('/', 'POST', [], [], [], [
            'REMOTE_ADDR' => '189.10.10.10',
            'HTTP_USER_AGENT' => 'TestAgent',
        ]);

        $result = $service->recusar('token_valido', $request, 'Motivo de teste');

        $this->assertTrue($result['success']);
        $this->assertSame(DocumentoAssinaturaSignatario::STATUS_RECUSADO, $signatario->updated['status']);
        $this->assertSame('Motivo de teste', $signatario->updated['recusa_motivo']);
    }
}

class FakeDocumentoAssinaturaSignatario extends DocumentoAssinaturaSignatario
{
    public array $updated = [];

    public function update(array $attributes = [], array $options = []): bool
    {
        $this->updated = $attributes;
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return true;
    }
}

class FakeDocumentoParaAssinatura extends DocumentoParaAssinatura
{
    public array $updated = [];

    public function update(array $attributes = [], array $options = []): bool
    {
        $this->updated = $attributes;
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return true;
    }
}
