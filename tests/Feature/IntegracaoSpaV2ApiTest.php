<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\User;
use App\Observers\ClienteIntegracaoSpaEmpresasAtivasCacheObserver;
use App\Support\IntegracaoSpa\IntegracaoSpaEmpresasAtivasCache;
use App\Support\IntegracaoSpa\IntegracaoSpaVagasAbertasPaginaCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Support\IntegracaoSpaV2Schema;
use Tests\TestCase;

class IntegracaoSpaV2ApiTest extends TestCase
{
    use IntegracaoSpaV2Schema;

    private const TOKEN = 'test-api-token-phpunit';

    private const ID_EMPRESA = 91001;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('X_API_TOKEN=' . self::TOKEN);
        $_SERVER['X_API_TOKEN'] = self::TOKEN;
        $_ENV['X_API_TOKEN'] = self::TOKEN;
    }

    public function testSemTokenRetorna403(): void
    {
        $this->getJson('/api/v2/integracao/')
            ->assertStatus(403)
            ->assertJsonFragment(['success' => false]);
    }

    public function testTokenInvalidoRetorna403(): void
    {
        $this->withHeader('X-API-TOKEN', 'token-errado')
            ->getJson('/api/v2/integracao/')
            ->assertStatus(403);
    }

    public function testEmpresasAtivasRetornaLista(): void
    {
        $this->garantirSchemaIntegracaoSpaV2();
        $this->limparDadosIntegracaoSpaV2();
        $this->seedEmpresaIntegracaoSpaV2();

        $response = $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->getJson('/api/v2/integracao/');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonFragment(['apelido' => 'acme-api-spa', 'razao_social' => 'ACME Ltda']);
    }

    public function testLogotipoIncluiLogoDateIsoUtc(): void
    {
        $this->garantirSchemaIntegracaoSpaV2();
        $this->limparDadosIntegracaoSpaV2();
        $this->seedEmpresaIntegracaoSpaV2();
        $this->seedClienteComLogoParaLogoDate();

        $response = $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->getJson('/api/v2/integracao/');

        $response->assertOk();
        $response->assertJsonPath('dados.0.logotipo.imagem', true);

        $logoDate = $response->json('dados.0.logotipo.logo_date');
        $this->assertIsString($logoDate);
        $this->assertStringEndsWith('Z', $logoDate);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $logoDate);
    }

    public function testEmpresasAtivasCacheInvalidaAoAtualizarCliente(): void
    {
        $this->garantirSchemaIntegracaoSpaV2();
        $this->limparDadosIntegracaoSpaV2();
        $this->seedEmpresaIntegracaoSpaV2();

        $url = '/api/v2/integracao/';

        $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->getJson($url)
            ->assertOk()
            ->assertJsonPath('dados.0.razao_social', 'ACME Ltda');

        $this->assertNotNull(Cache::get(IntegracaoSpaEmpresasAtivasCache::CACHE_KEY));

        DB::table('clientes')->where('id', self::ID_EMPRESA)->update([
            'razao_social' => 'ACME Renomeada SPA',
            'updated_at' => now(),
        ]);

        $cliente = Cliente::withoutGlobalScopes()->find(self::ID_EMPRESA);
        $this->assertNotNull($cliente);
        (new ClienteIntegracaoSpaEmpresasAtivasCacheObserver)->saved($cliente);

        $this->assertNull(Cache::get(IntegracaoSpaEmpresasAtivasCache::CACHE_KEY));

        $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->getJson($url)
            ->assertOk()
            ->assertJsonPath('dados.0.razao_social', 'ACME Renomeada SPA');
    }

    public function testEmpresaPreviewEVagas(): void
    {
        $this->garantirSchemaIntegracaoSpaV2();
        $this->limparDadosIntegracaoSpaV2();
        $this->seedEmpresaIntegracaoSpaV2();
        $this->seedVagaAbertaIntegracaoSpaV2('Vaga Aberta Teste');

        $response = $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->getJson('/api/v2/integracao/acme-api-spa');

        $response->assertOk();
        $response->assertJsonPath('dados.empresa.apelido', 'acme-api-spa');
        $response->assertJsonPath('dados.vagas_abertas.0.slug', 'vaga-aberta-teste');
        $response->assertJsonPath('dados.vagas_abertas.0.cargo.nome', 'Dev');
    }

    public function testVagasAbertasPaginadas(): void
    {
        $this->garantirSchemaIntegracaoSpaV2();
        $this->limparDadosIntegracaoSpaV2();
        $this->seedEmpresaIntegracaoSpaV2();

        $vagaCargo = DB::table('vagas')->insertGetId([
            'nome' => 'Cargo Pag',
            'ativo' => 1,
            'empresa_id' => self::ID_EMPRESA,
            'categoria_id' => null,
        ]);

        for ($i = 1; $i <= 52; $i++) {
            DB::table('vagas_abertas')->insert([
                'vaga_id' => $vagaCargo,
                'titulo' => "Vaga paginada {$i}",
                'descricao' => 'D',
                'municipio_id' => 1,
                'empresa_id' => self::ID_EMPRESA,
                'ativo' => 1,
                'ativo_sistema' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $page1 = $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->getJson('/api/v2/integracao/acme-api-spa/vagas-abertas?page=1');
        $page1->assertOk();
        $page1->assertJsonPath('dados.vagas_abertas.paginacao.por_pagina', 50);
        $page1->assertJsonPath('dados.vagas_abertas.paginacao.total', 52);
        $page1->assertJsonPath('dados.vagas_abertas.paginacao.ultima_pagina', 2);
        $this->assertCount(50, $page1->json('dados.vagas_abertas.itens'));

        $page2 = $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->getJson('/api/v2/integracao/acme-api-spa/vagas-abertas?page=2');
        $page2->assertOk();
        $this->assertCount(2, $page2->json('dados.vagas_abertas.itens'));
    }

    public function testVagasAbertasPaginacaoCacheMudaGeracaoAoBump(): void
    {
        $this->garantirSchemaIntegracaoSpaV2();
        $this->limparDadosIntegracaoSpaV2();
        $this->seedEmpresaIntegracaoSpaV2();

        $vagaCargo = DB::table('vagas')->insertGetId([
            'nome' => 'Cargo Pag',
            'ativo' => 1,
            'empresa_id' => self::ID_EMPRESA,
            'categoria_id' => null,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            DB::table('vagas_abertas')->insert([
                'vaga_id' => $vagaCargo,
                'titulo' => "Vaga cache {$i}",
                'descricao' => 'D',
                'municipio_id' => 1,
                'empresa_id' => self::ID_EMPRESA,
                'ativo' => 1,
                'ativo_sistema' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $porPagina = max(1, (int) config('integracao_spa.vagas_abertas_por_pagina', 50));
        $cacheKeyPag1 = 'integracao_spa:v2:vagas_page:'.self::ID_EMPRESA.':1:'.$porPagina.':g0';

        $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->getJson('/api/v2/integracao/acme-api-spa/vagas-abertas?page=1')
            ->assertOk();

        $this->assertNotNull(Cache::get($cacheKeyPag1));

        IntegracaoSpaVagasAbertasPaginaCache::bumpEmpresa(self::ID_EMPRESA);

        $cacheKeyPag1Gen1 = 'integracao_spa:v2:vagas_page:'.self::ID_EMPRESA.':1:'.$porPagina.':g1';

        $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->getJson('/api/v2/integracao/acme-api-spa/vagas-abertas?page=1')
            ->assertOk();

        $this->assertNotNull(Cache::get($cacheKeyPag1Gen1));
    }

    public function testRecrutamentoIntegracaoApelidoInvalidoRetorna404(): void
    {
        $this->garantirSchemaIntegracaoSpaV2();
        $this->limparDadosIntegracaoSpaV2();
        $this->seedEmpresaIntegracaoSpaV2();

        $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->postJson('/api/v2/integracao/empresa-inexistente-xyz/busca-cpf', ['cpf' => '529.982.247-25'])
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function testRecrutamentoIntegracaoBuscaCpfRetornaJsonArray(): void
    {
        $this->garantirSchemaIntegracaoSpaV2();
        $this->limparDadosIntegracaoSpaV2();
        $this->seedEmpresaIntegracaoSpaV2();

        $response = $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->postJson('/api/v2/integracao/acme-api-spa/busca-cpf', ['cpf' => '529.982.247-25']);

        $response->assertOk();
        $this->assertIsArray($response->json());
    }

    public function testDetalheVagaSlugInvalidoRetorna404(): void
    {
        $this->garantirSchemaIntegracaoSpaV2();
        $this->limparDadosIntegracaoSpaV2();
        $this->seedEmpresaIntegracaoSpaV2();
        $id = $this->seedVagaAbertaIntegracaoSpaV2('Título Real');

        $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->getJson('/api/v2/integracao/acme-api-spa/vagas-abertas/' . $id . '/slug-errado')
            ->assertStatus(404);
    }

    public function testDetalheVagaSlugCorretoRetorna200(): void
    {
        $this->garantirSchemaIntegracaoSpaV2();
        $this->limparDadosIntegracaoSpaV2();
        $this->seedEmpresaIntegracaoSpaV2();
        $id = $this->seedVagaAbertaIntegracaoSpaV2('Título Real');

        $this->withHeader('X-API-TOKEN', self::TOKEN)
            ->getJson('/api/v2/integracao/acme-api-spa/vagas-abertas/' . $id . '/titulo-real')
            ->assertOk()
            ->assertJsonPath('dados.vaga_aberta.titulo', 'Título Real');
    }

    private function seedEmpresaIntegracaoSpaV2(): void
    {
        DB::table('areas')->insertOrIgnore([
            'id' => 1,
            'label' => 'Área teste integração',
            'ativo' => 1,
        ]);

        DB::table('municipios')->insertOrIgnore([
            'id' => 1,
            'nome' => 'São Luís',
            'uf' => 'MA',
            'capital' => 0,
        ]);

        DB::table('users')->insert([
            'id' => self::ID_EMPRESA,
            'nome' => 'Empresa Teste API',
            'tipo' => User::EMPRESA,
            'ativo' => 1,
            'temp' => 0,
            'termos' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('clientes')->insert([
            'id' => self::ID_EMPRESA,
            'tipo_cliente' => 'Cliente',
            'tipo' => Cliente::TIPO_PESSOA_JURIDICA,
            'area_id' => 1,
            'ativo' => 1,
            'razao_social' => 'ACME Ltda',
            'apelido' => 'acme-api-spa',
            'missao' => 'Missão',
            'visao' => 'Visão',
            'valores' => 'Valores',
            'logradouro' => 'Rua A',
            'numero' => '1',
            'bairro' => 'Centro',
            'municipio' => 'São Luís',
            'uf' => 'MA',
            'cep' => '65000000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('vagas')->insert([
            'id' => 1,
            'nome' => 'Dev',
            'ativo' => 1,
            'empresa_id' => self::ID_EMPRESA,
            'categoria_id' => null,
        ]);
    }

    private function seedVagaAbertaIntegracaoSpaV2(string $titulo): int
    {
        return (int) DB::table('vagas_abertas')->insertGetId([
            'vaga_id' => 1,
            'titulo' => $titulo,
            'descricao' => 'Descrição',
            'municipio_id' => 1,
            'empresa_id' => self::ID_EMPRESA,
            'ativo' => 1,
            'ativo_sistema' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedClienteComLogoParaLogoDate(): void
    {
        $marcador = '2020-06-01 08:00:00';

        $arquivoId = (int) DB::table('arquivos')->insertGetId([
            'quem_enviou' => null,
            'nome' => 'logo.png',
            'imagem' => 1,
            'layout' => null,
            'extensao' => 'png',
            'file' => 'logos/test-spa-logo.png',
            'thumb' => null,
            'bytes' => 500,
            'temporario' => 0,
            'chave' => '',
            'disco' => Arquivo::DISCO_PUBLICO,
            'created_at' => $marcador,
            'updated_at' => $marcador,
        ]);

        DB::table('cliente_logotipo')->insert([
            'cliente_id' => self::ID_EMPRESA,
            'arquivo_id' => $arquivoId,
        ]);
    }
}
