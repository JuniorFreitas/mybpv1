<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use App\Support\IntegracaoSpa\IntegracaoSpaEmpresasAtivasCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Support\IntegracaoSpaV2Schema;
use Tests\TestCase;

class PortalVagasEmpresasPublicasTest extends TestCase
{
    use IntegracaoSpaV2Schema;

    private const TOKEN = 'test-api-token-phpunit';

    protected function setUp(): void
    {
        parent::setUp();

        putenv('X_API_TOKEN=' . self::TOKEN);
        $_SERVER['X_API_TOKEN'] = self::TOKEN;
        $_ENV['X_API_TOKEN'] = self::TOKEN;
        putenv('X_API_TOKEN_SCOPES=spa_read,spa_write');
        $_SERVER['X_API_TOKEN_SCOPES'] = 'spa_read,spa_write';
        $_ENV['X_API_TOKEN_SCOPES'] = 'spa_read,spa_write';
    }

    public function testSemTokenRetorna403(): void
    {
        $this->getJson('/api/portal-vagas/empresas')
            ->assertStatus(403)
            ->assertJsonFragment(['success' => false]);
    }

    public function testComSpaReadExpoeApenasCamposPermitidos(): void
    {
        $this->garantirSchemaIntegracaoSpaV2();

        $resp = $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->getJson('/api/portal-vagas/empresas')
            ->assertOk()
            ->assertJsonFragment(['success' => true]);

        foreach ($resp->json('dados') ?? [] as $item) {
            $this->assertEqualsCanonicalizing(
                ['apelido', 'nome', 'municipio', 'uf', 'logo_url', 'total_vagas'],
                array_keys($item)
            );
        }
    }

    public function testVitrineOmiteApelidosInternosBpserviceEMybp(): void
    {
        $this->garantirSchemaIntegracaoSpaV2();
        $this->limparDadosIntegracaoSpaV2();
        IntegracaoSpaEmpresasAtivasCache::forget();
        Cache::forget('portal_vagas:contagem_por_empresa');

        DB::table('areas')->insertOrIgnore([
            'id' => 1,
            'label' => 'Área portal vagas',
            'ativo' => 1,
        ]);

        $empresas = [
            92001 => ['apelido' => 'acme-portal', 'razao_social' => 'ACME Portal'],
            92002 => ['apelido' => '_bpservice', 'razao_social' => 'BP Service'],
            92003 => ['apelido' => 'mybp', 'razao_social' => 'MyBP Interno'],
        ];

        foreach ($empresas as $id => $meta) {
            DB::table('users')->insert([
                'id' => $id,
                'nome' => $meta['razao_social'],
                'tipo' => User::EMPRESA,
                'ativo' => 1,
                'temp' => 0,
                'termos' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('clientes')->insert([
                'id' => $id,
                'tipo_cliente' => 'Cliente',
                'tipo' => Cliente::TIPO_PESSOA_JURIDICA,
                'area_id' => 1,
                'ativo' => 1,
                'razao_social' => $meta['razao_social'],
                'apelido' => $meta['apelido'],
                'municipio' => 'São Luís',
                'uf' => 'MA',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $apelidos = collect(
            $this->withHeader('X-API-TOKEN', self::TOKEN)
                ->getJson('/api/portal-vagas/empresas')
                ->assertOk()
                ->json('dados')
        )->pluck('apelido')->all();

        $this->assertContains('acme-portal', $apelidos);
        $this->assertNotContains('_bpservice', $apelidos);
        $this->assertNotContains('mybp', $apelidos);
    }
}
