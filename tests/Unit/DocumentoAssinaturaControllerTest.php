<?php

namespace Tests\Unit;

use App\Http\Controllers\DocumentoAssinaturaController;
use App\Models\DocumentoAssinaturaEvento;
use App\Services\AssinaturaDigital\AssinaturaCotaService;
use App\Services\AssinaturaDigital\AssinaturaDigitalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class DocumentoAssinaturaControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testExportarEvidenciasRegistraEventoEAplicaMascara(): void
    {
        $signatario = (object) [
            'id' => 1,
            'user_id' => 10,
            'email' => 'teste@mybp.com.br',
            'nome' => 'Teste',
            'cpf' => '111.444.777-35',
            'ordem' => 1,
            'token' => 'token',
            'status' => 'assinado',
            'ip' => '189.10.10.10',
            'user_agent' => 'TestAgent',
            'data_assinatura_utc' => Carbon::parse('2026-03-02 12:00:00', 'UTC'),
            'geolocalizacao' => ['city' => 'São Paulo'],
            'hash_evidencia' => 'hash',
            'recusa_motivo' => null,
            'consentimento_assinatura' => true,
            'consentimento_em' => Carbon::parse('2026-03-02 12:01:00', 'UTC'),
        ];

        $evento = (object) [
            'id' => 99,
            'evento' => DocumentoAssinaturaEvento::EVENTO_ASSINADO,
            'payload' => ['ip' => '189.10.10.10'],
            'created_at' => Carbon::parse('2026-03-02 12:02:00', 'UTC'),
        ];

        $doc = new class() {
            public int $id = 123;
            public string $token = 'doc-token';
            public string $tipo_documento = 'documento_demissao';
            public string $status = 'concluido';
            public ?string $hash_sha256 = 'hashdoc';
            public ?int $arquivo_id = 1;
            public ?int $arquivo_assinado_id = 2;
            public ?int $solicitante_id = 3;
            public ?int $empresa_id = 104;
            public ?\Carbon\Carbon $data_expiracao = null;
            public ?\Carbon\Carbon $consentimento_ultimo_em = null;
            public ?int $consentimento_ultimo_signatario_id = 1;
            public Collection $signatarios;
            public Collection $eventos;
        };

        $doc->signatarios = collect([$signatario]);
        $doc->eventos = collect([$evento]);

        $builder = new class($doc) {
            private $doc;
            public function __construct($doc)
            {
                $this->doc = $doc;
            }
            public function where($column, $value)
            {
                return $this;
            }
            public function porIdOuToken($id)
            {
                return $this;
            }
            public function firstOrFail()
            {
                return $this->doc;
            }
        };

        $docAlias = Mockery::mock('alias:App\\Models\\DocumentoParaAssinatura');
        $docAlias->shouldReceive('with')->andReturn($builder);

        $config = (object) [
            'assinatura_exibir_ip_completo' => false,
            'assinatura_exibir_cpf_completo' => false,
        ];
        $configAlias = Mockery::mock('alias:App\\Models\\ClienteConfig');
        $configAlias->shouldReceive('whereClienteId')->with(104)->andReturnSelf();
        $configAlias->shouldReceive('first')->andReturn($config);

        $service = Mockery::mock(AssinaturaDigitalService::class);
        $service->shouldReceive('registrarEvento')
            ->once()
            ->withArgs(function ($docId, $evento, $payload) {
                $this->assertSame(123, $docId);
                $this->assertSame(DocumentoAssinaturaEvento::EVENTO_EXPORTADO, $evento);
                $this->assertSame('json', $payload['formato']);
                $this->assertArrayHasKey('ip', $payload);
                return true;
            });

        $cotaService = Mockery::mock(AssinaturaCotaService::class);
        $controller = new DocumentoAssinaturaController($service, $cotaService);

        Auth::shouldReceive('user')->andReturn((object) [
            'id' => 999,
            'empresa_id' => 104,
            'nome' => 'Admin',
            'email' => 'admin@mybp.com.br',
        ]);

        $request = Request::create('/g/documento-assinatura/123/evidencias', 'GET');
        $response = $controller->exportarEvidencias($request, 123);
        $data = $response->getData(true);

        $this->assertSame('***.***.***-**', $data['signatarios'][0]['cpf']);
        $this->assertSame('189.10.10.***', $data['signatarios'][0]['ip']);
    }
}
