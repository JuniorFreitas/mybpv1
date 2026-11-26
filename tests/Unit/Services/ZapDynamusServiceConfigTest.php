<?php

namespace Tests\Unit\Services;

use App\Services\Dynamus\ZapDynamusService;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

class ZapDynamusServiceConfigTest extends TestCase
{
    #[Test]
    public function usa_apikey_de_config_e_nao_hardcode(): void
    {
        config([
            'services.zapdynamus.api_key' => 'chave-de-teste',
            'services.zapdynamus.base_url' => 'https://example.test',
            'services.zapdynamus.instancia' => 'instancia-teste',
        ]);

        $service = new ZapDynamusService();
        $ref = new ReflectionClass($service);

        $baseUrl = $ref->getProperty('baseUrl');
        $baseUrl->setAccessible(true);
        $this->assertSame('https://example.test', $baseUrl->getValue($service));

        $instancia = $ref->getProperty('instancia');
        $instancia->setAccessible(true);
        $this->assertSame('instancia-teste', $instancia->getValue($service));

        $client = $ref->getProperty('client');
        $client->setAccessible(true);
        $headers = $client->getValue($service)->getConfig('headers');
        $this->assertSame('chave-de-teste', $headers['apikey'] ?? null);

        $source = file_get_contents(base_path('app/Services/Dynamus/ZapDynamusService.php'));
        $this->assertStringNotContainsString('20c5dee2915cfa8064a635434c118a60', $source);
    }
}
