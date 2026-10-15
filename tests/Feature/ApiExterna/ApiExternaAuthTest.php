<?php

namespace Tests\Feature\ApiExterna;

use App\Support\ApiExterna\ApiExternaScopes;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Support\ApiExternaSchema;
use Tests\TestCase;

class ApiExternaAuthTest extends TestCase
{
    use ApiExternaSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->garantirSchemaApiExterna();
    }

    public function testTokenComCredenciaisValidas(): void
    {
        $cred = $this->criarCredencialApiExterna();

        $this->postJson('/api/v3/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $cred['client_id'],
            'client_secret' => $cred['client_secret'],
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure(['access_token', 'expires_in', 'scope']);
    }

    public function testTokenComSecretInvalidoRetorna401(): void
    {
        $cred = $this->criarCredencialApiExterna();

        $this->postJson('/api/v3/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $cred['client_id'],
            'client_secret' => 'secret-errado',
        ])
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function testRecursoSemBearerRetorna401(): void
    {
        $this->getJson('/api/v3/cloud')
            ->assertStatus(401);
    }

    public function testScopeInsuficienteRetorna403(): void
    {
        $cred = $this->criarCredencialApiExterna(91002, [ApiExternaScopes::FORNECEDORES_READ]);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/cloud')
            ->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function testIsolamentoPorEmpresa(): void
    {
        $credA = $this->criarCredencialApiExterna(92001);
        $credB = $this->criarCredencialApiExterna(92002);

        DB::table('clouds')->insert([
            [
                'empresa_id' => 92001,
                'nome' => 'Cloud A',
                'slug' => 'cloud-a',
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => 92002,
                'nome' => 'Cloud B',
                'slug' => 'cloud-b',
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $tokenA = $this->bearerToken($credA['client_id'], $credA['client_secret']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->getJson('/api/v3/cloud');

        $response->assertOk();
        $data = collect($response->json('data'));
        $nomes = $data->pluck('nome')->all();
        $this->assertContains('Cloud A', $nomes);
        $this->assertNotContains('Cloud B', $nomes);
        $this->assertArrayHasKey('public_id', $data->first());
        $this->assertArrayNotHasKey('empresa_id', $data->first());
        $this->assertArrayNotHasKey('id', $data->first());
    }

    public function testListagemFornecedoresComScope(): void
    {
        $cred = $this->criarCredencialApiExterna(93001);
        DB::table('fornecedores')->insert([
            'empresa_id' => 93001,
            'tipo' => 'Fornecedor',
            'tipo_pessoa' => 'Juridica',
            'nome' => 'Fornecedor Teste',
            'razao_social' => 'Fornecedor Teste LTDA',
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/fornecedores');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['nome' => 'Fornecedor Teste']);

        $item = $response->json('data.0');
        $this->assertArrayHasKey('public_id', $item);
        $this->assertArrayNotHasKey('empresa_id', $item);
        $this->assertArrayNotHasKey('id', $item);
    }

    public function testDocsOpenapiDisponivel(): void
    {
        $this->get('/api/v3/docs')->assertOk();
    }

    public function testValidacaoTokenRetornaJson(): void
    {
        $this->post('/api/v3/oauth/token', [])
            ->assertStatus(422)
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'msg', 'errors']);
    }

    public function testErro500RetornaJsonSemHtml(): void
    {
        config(['app.debug' => true]);

        // Simula client sem Accept JSON (antes recebia HTML/Whoops)
        $request = Request::create('/api/v3/processos/ativos', 'GET');
        $request->headers->set('Accept', 'text/html');

        $response = app(ExceptionHandler::class)->render(
            $request,
            new RuntimeException('falha-controlada')
        );

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('application/json', (string) $response->headers->get('content-type'));
        $this->assertSame([
            'success' => false,
            'msg' => 'falha-controlada',
        ], $response->getData(true));
        $this->assertStringNotContainsString('<html', strtolower($response->getContent()));
    }

    public function testGetDetalheUsaPublicIdUuidENaoInteger(): void
    {
        $cred = $this->criarCredencialApiExterna(94001);
        $cloudId = DB::table('clouds')->insertGetId([
            'empresa_id' => 94001,
            'nome' => 'Cloud UUID',
            'slug' => 'cloud-uuid',
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);

        $lista = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/cloud')
            ->assertOk();

        $publicId = $lista->json('data.0.public_id');
        $this->assertIsString($publicId);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $publicId
        );

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/cloud/' . $publicId)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nome', 'Cloud UUID')
            ->assertJsonPath('data.public_id', $publicId)
            ->assertJsonMissingPath('data.id')
            ->assertJsonMissingPath('data.empresa_id');

        // ID numérico não casa com a rota UUID → 404 (fallback)
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/cloud/' . $cloudId)
            ->assertStatus(404);
    }
}
