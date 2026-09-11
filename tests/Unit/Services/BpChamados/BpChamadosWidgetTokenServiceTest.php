<?php

namespace Tests\Unit\Services\BpChamados;

use App\Models\User;
use App\Services\BpChamados\BpChamadosWidgetTokenService;
use RuntimeException;
use Tests\TestCase;

class BpChamadosWidgetTokenServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.bp_chamados.enabled' => true,
            'services.bp_chamados.api_base_url' => 'http://localhost:8100',
            'services.bp_chamados.application_id' => '01TESTAPPIDPUBLICULIDXX',
            'services.bp_chamados.widget_secret' => 'ws_test_secret_unit',
            'services.bp_chamados.token_ttl' => 900,
        ]);
    }

    public function testMintGeraJwtHs256ComClaimsObrigatorios(): void
    {
        $user = new User();
        $user->id = 42;
        $user->nome = 'Maria Silva';
        $user->login = 'maria@empresa.com';
        $user->setRelation('Empresa', null);

        $service = new BpChamadosWidgetTokenService();
        $result = $service->mintFor($user);

        $this->assertArrayHasKey('token', $result);
        $this->assertSame(900, $result['expires_in']);

        $parts = explode('.', $result['token']);
        $this->assertCount(3, $parts);

        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        $this->assertSame('01TESTAPPIDPUBLICULIDXX', $payload['iss']);
        $this->assertSame('42', $payload['sub']);
        $this->assertSame('Maria Silva', $payload['name']);
        $this->assertSame('maria@empresa.com', $payload['email']);
        $this->assertArrayNotHasKey('company', $payload);
        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('exp', $payload);
        $this->assertGreaterThan($payload['iat'], $payload['exp']);

        $expectedSig = rtrim(strtr(base64_encode(
            hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", 'ws_test_secret_unit', true)
        ), '+/', '-_'), '=');
        $this->assertSame($expectedSig, $parts[2]);
    }

    public function testMintIncluiCompanyDaEmpresaDoUsuario(): void
    {
        $empresa = new \App\Models\Cliente();
        $empresa->forceFill([
            'id' => 584,
            'nome_fantasia' => 'Empresa XPTO',
            'razao_social' => 'XPTO LTDA',
            'apelido' => 'XPTO',
        ]);

        $user = new User();
        $user->id = 42;
        $user->nome = 'Maria Silva';
        $user->login = 'maria@empresa.com';
        $user->setRelation('Empresa', $empresa);

        $payload = json_decode(
            $this->base64UrlDecode(explode('.', (new BpChamadosWidgetTokenService())->mintFor($user)['token'])[1]),
            true
        );

        $this->assertSame([
            'id' => '584',
            'name' => 'Empresa XPTO',
            'alias' => 'XPTO',
        ], $payload['company']);
    }

    public function testMintFalhaQuandoNaoConfigurado(): void
    {
        config([
            'services.bp_chamados.widget_secret' => null,
        ]);

        $user = new User();
        $user->id = 1;
        $user->nome = 'Teste';
        $user->login = 'teste@x.com';
        $user->setRelation('Empresa', null);

        $this->expectException(RuntimeException::class);

        (new BpChamadosWidgetTokenService())->mintFor($user);
    }

    public function testIsEnabledExigeFlagEConfigCompleta(): void
    {
        $service = new BpChamadosWidgetTokenService();
        $this->assertTrue($service->isEnabled());

        config(['services.bp_chamados.enabled' => false]);
        $this->assertFalse($service->isEnabled());
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'), true);
    }
}
