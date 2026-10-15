<?php

namespace Tests\Feature\ApiExterna;

use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaScopes;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApiExternaSchema;
use Tests\TestCase;

class ApiExternaFornecedorTest extends TestCase
{
    use ApiExternaSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->garantirSchemaApiExterna();
    }

    public function testListagemEnriquecidaComTelefonesEServicos(): void
    {
        $empresaId = 96001;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::FORNECEDORES_READ]);
        $fornecedorId = $this->seedFornecedor($empresaId);

        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);

        $item = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/fornecedores')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->json('data.0');

        $this->assertSame(ApiExternaId::encode($fornecedorId), $item['public_id']);
        $this->assertArrayNotHasKey('empresa_id', $item);
        $this->assertArrayNotHasKey('id', $item);
        $this->assertSame('***.***.***-99', $item['cpf']);
        $this->assertNotEmpty($item['endereco_completo']);
        $this->assertSame('98988887777', $item['telefones'][0]['numero']);
        $this->assertSame('Consultoria', $item['servicos'][0]['tipo_servico']['label']);
        $this->assertSame('Iniciado', $item['servicos'][0]['status']);
        $this->assertArrayNotHasKey('anexos', $item['servicos'][0]);
    }

    public function testDetalheIncluiAnexosMetadados(): void
    {
        $empresaId = 96002;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::FORNECEDORES_READ]);
        $fornecedorId = $this->seedFornecedor($empresaId);
        $publicId = ApiExternaId::encode($fornecedorId);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);

        $data = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/fornecedores/' . $publicId)
            ->assertOk()
            ->json('data');

        $this->assertArrayHasKey('anexos', $data);
        $this->assertSame('contrato.pdf', $data['anexos'][0]['nome']);
        $this->assertArrayHasKey('public_id', $data['anexos'][0]);
        $this->assertArrayNotHasKey('chave', $data['anexos'][0]);
        $this->assertArrayNotHasKey('file', $data['anexos'][0]);
        $this->assertArrayHasKey('anexos', $data['servicos'][0]);
        $this->assertSame('escopo.pdf', $data['servicos'][0]['anexos'][0]['nome']);
        $this->assertArrayHasKey('url_download', $data['anexos'][0]);
        $this->assertArrayHasKey('servicos', $data['links']);
    }

    public function testBuscarTiposServicosELinks(): void
    {
        $empresaId = 96003;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::FORNECEDORES_READ]);
        $fornecedorId = $this->seedFornecedor($empresaId);
        $publicId = ApiExternaId::encode($fornecedorId);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);
        $auth = ['Authorization' => 'Bearer ' . $token];

        $busca = $this->withHeaders($auth)
            ->getJson('/api/v3/fornecedores/buscar?q=Rico')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $busca);
        $this->assertSame($publicId, $busca[0]['public_id']);

        $this->withHeaders($auth)
            ->getJson('/api/v3/fornecedores/buscar?q=x')
            ->assertStatus(422);

        $tipos = $this->withHeaders($auth)
            ->getJson('/api/v3/fornecedores/tipos-servico')
            ->assertOk()
            ->json('data');
        $this->assertNotEmpty($tipos);
        $this->assertSame('Consultoria', $tipos[0]['label']);

        $servicos = $this->withHeaders($auth)
            ->getJson('/api/v3/fornecedores/' . $publicId . '/servicos')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $servicos);
        $this->assertArrayHasKey('self', $servicos[0]['links']);

        $servico = $this->withHeaders($auth)
            ->getJson('/api/v3/fornecedores/' . $publicId . '/servicos/' . $servicos[0]['public_id'])
            ->assertOk()
            ->json('data');
        $this->assertSame('Iniciado', $servico['status']);
        $this->assertStringContainsString('/download', $servico['anexos'][0]['url_download']);

        $filtrado = $this->withHeaders($auth)
            ->getJson('/api/v3/fornecedores?tipo=Fornecedor&uf=MA')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $filtrado);
    }

    private function seedFornecedor(int $empresaId): int
    {
        $fornecedorId = DB::table('fornecedores')->insertGetId([
            'empresa_id' => $empresaId,
            'tipo' => 'Fornecedor',
            'tipo_pessoa' => 'pessoa_física',
            'nome' => 'Fornecedor Rico',
            'cpf' => '12345678999',
            'email' => 'fornecedor@example.com',
            'contato' => 'Maria',
            'cep' => '65000000',
            'logradouro' => 'Rua A',
            'numero' => '100',
            'bairro' => 'Centro',
            'municipio' => 'São Luís',
            'uf' => 'MA',
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('usuarios_telefone')->insert([
            'user_id' => $fornecedorId,
            'tipo' => 'whatsapp',
            'pais' => '55',
            'numero' => '98988887777',
            'principal' => true,
        ]);

        $tipoServicoId = DB::table('tipo_servico_fornecedor')->insertGetId([
            'label' => 'Consultoria',
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $servicoId = DB::table('fornecedor_servico')->insertGetId([
            'fornecedor_id' => $fornecedorId,
            'tipo_servico_fornecedor_id' => $tipoServicoId,
            'status' => 'Iniciado',
            'escopo' => 'Apoio RH',
            'valor' => 'R$ 500,00 a R$ 1.000,00',
            'ativo' => true,
        ]);

        $anexoFornecedor = DB::table('arquivos')->insertGetId([
            'nome' => 'contrato.pdf',
            'extensao' => 'pdf',
            'bytes' => 1024,
            'file' => 'contrato-' . $empresaId . '.pdf',
            'disco' => 'disco-fornecedor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('fornecedor_anexos')->insert([
            'fornecedor_id' => $fornecedorId,
            'arquivo_id' => $anexoFornecedor,
        ]);

        $anexoServico = DB::table('arquivos')->insertGetId([
            'nome' => 'escopo.pdf',
            'extensao' => 'pdf',
            'bytes' => 2048,
            'file' => 'escopo-' . $empresaId . '.pdf',
            'disco' => 'disco-servicofornecedor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('fornecedor_servico_anexos')->insert([
            'fornecedor_servico_id' => $servicoId,
            'arquivo_id' => $anexoServico,
        ]);

        return $fornecedorId;
    }
}
