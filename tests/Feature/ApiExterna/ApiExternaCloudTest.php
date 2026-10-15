<?php

namespace Tests\Feature\ApiExterna;

use App\Models\GrupoCloud;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaScopes;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApiExternaSchema;
use Tests\TestCase;

class ApiExternaCloudTest extends TestCase
{
    use ApiExternaSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->garantirSchemaApiExterna();
    }

    public function testListagemCloudComContagens(): void
    {
        $empresaId = 97001;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::CLOUD_READ]);
        $cloudId = $this->seedCloud($empresaId);

        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);

        $item = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/cloud')
            ->assertOk()
            ->json('data.0');

        $this->assertSame(ApiExternaId::encode($cloudId), $item['public_id']);
        $this->assertSame(1, $item['usuarios_count']);
        $this->assertSame(2, $item['itens_count']);
        $this->assertArrayNotHasKey('empresa_id', $item);
    }

    public function testDetalheCloudComMembrosEGrupos(): void
    {
        $empresaId = 97002;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::CLOUD_READ]);
        $cloudId = $this->seedCloud($empresaId);
        $publicId = ApiExternaId::encode($cloudId);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);

        $data = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/cloud/' . $publicId)
            ->assertOk()
            ->json('data');

        $this->assertSame('Cloud Rico', $data['nome']);
        $this->assertCount(1, $data['usuarios']);
        $this->assertTrue($data['usuarios'][0]['administrador']);
        $this->assertSame(GrupoCloud::NOME_ADMINISTRADORES, $data['usuarios'][0]['grupo_nome']);
        $this->assertNotEmpty($data['grupos']);
        $this->assertSame('Operacional', $data['grupos'][0]['nome']);
    }

    public function testItensEnriquecidosEFiltroPorPasta(): void
    {
        $empresaId = 97003;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::CLOUD_READ]);
        $seed = $this->seedCloud($empresaId);
        $cloudPublicId = ApiExternaId::encode($seed);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);

        $raiz = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/cloud/' . $cloudPublicId . '/itens')
            ->assertOk()
            ->json('data');

        $this->assertCount(1, $raiz);
        $this->assertSame('pasta', $raiz[0]['tipo']);
        $this->assertSame('Documentos', $raiz[0]['label']);
        $this->assertNotEmpty($raiz[0]['criou']['nome']);
        $this->assertArrayHasKey('permissoes', $raiz[0]);

        $pastaPublicId = $raiz[0]['public_id'];

        $dentro = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/cloud/' . $cloudPublicId . '/itens?pasta_public_id=' . $pastaPublicId)
            ->assertOk()
            ->json('data');

        $this->assertCount(1, $dentro);
        $this->assertSame('arquivo', $dentro[0]['tipo']);
        $this->assertSame('manual.pdf', $dentro[0]['arquivo']['nome']);
        $this->assertArrayNotHasKey('file', $dentro[0]['arquivo']);
        $this->assertArrayNotHasKey('chave', $dentro[0]['arquivo']);

        $this->assertArrayHasKey('children', $raiz[0]['links']);
        $this->assertStringContainsString('pasta_public_id=', $raiz[0]['links']['children']);

        $this->assertArrayHasKey('url_download', $dentro[0]['links']);
        $this->assertArrayHasKey('urlDownload', $dentro[0]['arquivo']);
        $this->assertStringContainsString('/download', $dentro[0]['links']['url_download']);
        $this->assertSame($dentro[0]['links']['url_download'], $dentro[0]['arquivo']['url_download']);
    }

    public function testBuscarPastasResolverPathEDetalheItem(): void
    {
        $empresaId = 97004;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::CLOUD_READ]);
        $cloudId = $this->seedCloud($empresaId);
        $cloudPublicId = ApiExternaId::encode($cloudId);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);
        $auth = ['Authorization' => 'Bearer ' . $token];

        $busca = $this->withHeaders($auth)
            ->getJson('/api/v3/cloud/' . $cloudPublicId . '/buscar?q=Manual')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $busca);
        $this->assertSame('arquivo', $busca[0]['tipo']);

        $this->withHeaders($auth)
            ->getJson('/api/v3/cloud/' . $cloudPublicId . '/buscar?q=x')
            ->assertStatus(422);

        $pastas = $this->withHeaders($auth)
            ->getJson('/api/v3/cloud/' . $cloudPublicId . '/pastas')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $pastas);
        $this->assertSame(1, $pastas[0]['filhos_arquivos_count']);
        $this->assertArrayHasKey('pastas', $pastas[0]['links']);

        $path = $this->withHeaders($auth)
            ->getJson('/api/v3/cloud/' . $cloudPublicId . '/resolver-path?path=documentos')
            ->assertOk()
            ->json('data');
        $this->assertSame($pastas[0]['public_id'], $path['pasta_public_id']);
        $this->assertSame('documentos', $path['path']);

        $item = $this->withHeaders($auth)
            ->getJson('/api/v3/cloud/' . $cloudPublicId . '/itens/' . $busca[0]['public_id'])
            ->assertOk()
            ->json('data');
        $this->assertArrayHasKey('caminho', $item);
        $this->assertSame('documentos', $item['path']);
        $this->assertSame('Manual', $item['label']);

        $filtrado = $this->withHeaders($auth)
            ->getJson('/api/v3/cloud/' . $cloudPublicId . '/itens?tipo=pasta')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $filtrado);
        $this->assertSame('pasta', $filtrado[0]['tipo']);
    }

    private function seedCloud(int $empresaId): int
    {
        $adminGrupoId = DB::table('grupo_clouds')->insertGetId([
            'nome' => GrupoCloud::NOME_ADMINISTRADORES,
            'descricao' => 'Admins',
            'ativo' => true,
            'empresa_id' => $empresaId,
        ]);

        DB::table('grupo_clouds')->insert([
            'nome' => 'Operacional',
            'descricao' => 'Ops',
            'ativo' => true,
            'empresa_id' => $empresaId,
        ]);

        $userId = DB::table('users')->insertGetId([
            'nome' => 'Admin Cloud',
            'login' => 'admin-cloud-' . $empresaId,
            'password' => bcrypt('secret'),
            'tipo' => 'Admin',
            'ativo' => true,
            'empresa_id' => $empresaId,
            'grupo_cloud_id' => $adminGrupoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cloudId = DB::table('clouds')->insertGetId([
            'empresa_id' => $empresaId,
            'nome' => 'Cloud Rico',
            'slug' => 'cloud-rico-' . $empresaId,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_clouds')->insert([
            'user_id' => $userId,
            'cloud_id' => $cloudId,
        ]);

        $pastaId = DB::table('itens_cloud')->insertGetId([
            'cloud_id' => $cloudId,
            'label' => 'Documentos',
            'tipo' => 'pasta',
            'pertence' => null,
            'quem_criou' => $userId,
            'aprovado' => true,
            'revisado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('permissoes_itens_clouds')->insert([
            'item_id' => $pastaId,
            'grupo_cloud_id' => $adminGrupoId,
        ]);

        $arquivoId = DB::table('arquivos')->insertGetId([
            'nome' => 'manual.pdf',
            'extensao' => 'pdf',
            'bytes' => 4096,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('itens_cloud')->insert([
            'cloud_id' => $cloudId,
            'arquivo_id' => $arquivoId,
            'label' => 'Manual',
            'tipo' => 'arquivo',
            'pertence' => $pastaId,
            'quem_criou' => $userId,
            'aprovado' => true,
            'revisado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $cloudId;
    }
}
