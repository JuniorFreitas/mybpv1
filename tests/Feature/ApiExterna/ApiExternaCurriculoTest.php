<?php

namespace Tests\Feature\ApiExterna;

use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaScopes;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApiExternaSchema;
use Tests\TestCase;

class ApiExternaCurriculoTest extends TestCase
{
    use ApiExternaSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->garantirSchemaProcessosApiExterna();
    }

    public function testListagemEnriquecidaComLinksEFiltros(): void
    {
        $empresaId = 99001;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::CURRICULOS_READ]);
        $seed = $this->seedCurriculo($empresaId);

        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);
        $auth = ['Authorization' => 'Bearer ' . $token];

        $item = $this->withHeaders($auth)
            ->getJson('/api/v3/curriculos')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->json('data.0');

        $this->assertSame(ApiExternaId::encode($seed['curriculo_id']), $item['public_id']);
        $this->assertArrayNotHasKey('id', $item);
        $this->assertSame('Ana Curriculo', $item['nome']);
        $this->assertSame('***.***.***-88', $item['cpf']);
        $this->assertSame(1, $item['feedbacks_count']);
        $this->assertSame(ApiExternaId::encode($seed['feedback_id']), $item['ultimo_feedback']['public_id']);
        $this->assertArrayHasKey('feedbacks', $item['links']);

        $filtrado = $this->withHeaders($auth)
            ->getJson('/api/v3/curriculos?q=Ana&uf=MA&pcd=1')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $filtrado);

        $busca = $this->withHeaders($auth)
            ->getJson('/api/v3/curriculos/buscar?q=Ana')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $busca);

        $this->withHeaders($auth)
            ->getJson('/api/v3/curriculos/buscar?q=x')
            ->assertStatus(422);
    }

    public function testDetalheENested(): void
    {
        $empresaId = 99002;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::CURRICULOS_READ]);
        $seed = $this->seedCurriculo($empresaId);
        $publicId = ApiExternaId::encode($seed['curriculo_id']);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);
        $auth = ['Authorization' => 'Bearer ' . $token];

        $detalhe = $this->withHeaders($auth)
            ->getJson('/api/v3/curriculos/' . $publicId)
            ->assertOk()
            ->json('data');

        $this->assertSame('Ana Curriculo', $detalhe['nome']);
        $this->assertSame('Superior', $detalhe['escolaridade']['tipo']);
        $this->assertSame('98911112222', $detalhe['telefones'][0]['numero']);
        $this->assertSame('***.***.***-77', $detalhe['dependentes'][0]['cpf']);
        $this->assertCount(1, $detalhe['feedbacks']);
        $this->assertSame(1, $detalhe['experiencias_count']);
        $this->assertSame(1, $detalhe['documentos_count']);
        $this->assertArrayHasKey('documentos', $detalhe['links']);
        $this->assertArrayHasKey('experiencias', $detalhe['links']);

        $feedbacks = $this->withHeaders($auth)
            ->getJson('/api/v3/curriculos/' . $publicId . '/feedbacks')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $feedbacks);
        $this->assertStringContainsString('/processos/', $feedbacks[0]['links']['self']);

        $docs = $this->withHeaders($auth)
            ->getJson('/api/v3/curriculos/' . $publicId . '/documentos')
            ->assertOk()
            ->json('data');
        $this->assertSame('anexoscpfrg', $docs['documentos'][0]['tipo']);
        $this->assertArrayNotHasKey('file', $docs['documentos'][0]['arquivos'][0]);

        $exps = $this->withHeaders($auth)
            ->getJson('/api/v3/curriculos/' . $publicId . '/experiencias')
            ->assertOk()
            ->json('data');
        $this->assertSame('Empresa X', $exps['experiencias'][0]['empresa']);
        $this->assertArrayNotHasKey('referencia_nome', $exps['experiencias'][0]);
        $this->assertArrayNotHasKey('referencia_telefone', $exps['experiencias'][0]);

        $outraEmpresa = $this->criarCredencialApiExterna(99099, [ApiExternaScopes::CURRICULOS_READ]);
        $tokenOutro = $this->bearerToken($outraEmpresa['client_id'], $outraEmpresa['client_secret']);
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenOutro)
            ->getJson('/api/v3/curriculos')
            ->assertOk()
            ->assertJsonPath('data', []);
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenOutro)
            ->getJson('/api/v3/curriculos/' . $publicId)
            ->assertStatus(404);
    }

    /**
     * @return array{curriculo_id:int,feedback_id:int,vaga_id:int}
     */
    private function seedCurriculo(int $empresaId): array
    {
        $escolaridadeId = DB::table('escolaridades')->insertGetId(['tipo' => 'Superior']);
        $areaId = DB::table('areas')->insertGetId(['label' => 'RH', 'ativo' => true]);
        $clienteId = DB::table('clientes')->insertGetId([
            'razao_social' => 'Cliente Curriculo',
            'nome' => 'Cliente C',
            'cnpj' => '11222333000144',
            'area_id' => $areaId,
        ]);
        $vagaId = DB::table('vagas')->insertGetId([
            'nome' => 'Analista',
            'ativo' => true,
            'empresa_id' => $empresaId,
        ]);
        $municipioId = DB::table('municipios')->insertGetId(['nome' => 'São Luís', 'uf' => 'MA']);
        $vagaAbertaId = DB::table('vagas_abertas')->insertGetId([
            'empresa_id' => $empresaId,
            'vaga_id' => $vagaId,
            'titulo' => 'Analista - SLZ',
            'municipio_id' => $municipioId,
            'ativo' => true,
        ]);

        $curriculoId = DB::table('curriculos')->insertGetId([
            'nome' => 'Ana Curriculo',
            'cpf' => '11122233388',
            'email' => 'ana@example.com',
            'sexo' => 'F',
            'municipio' => 'São Luís',
            'uf' => 'MA',
            'uf_vaga' => 'MA',
            'pcd' => true,
            'lido' => true,
            'formacao' => $escolaridadeId,
            'formacao_curso' => 'Administração',
            'formacao_status' => 'Completo',
            'vaga_pretendida' => $vagaId,
            'logradouro' => 'Rua A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('curriculo_telefone')->insert([
            'curriculo_id' => $curriculoId,
            'numero' => '98911112222',
            'pais' => '55',
            'tipo' => 'whatsapp',
            'principal' => true,
        ]);

        DB::table('usuario_dependentes')->insert([
            'user_id' => $curriculoId,
            'nome' => 'Filho Ana',
            'nascimento' => '2015-01-01',
            'tipo' => 'filho',
            'cpf' => '99988877777',
        ]);

        DB::table('curriculo_experiencias')->insert([
            'curriculo_id' => $curriculoId,
            'empresa' => 'Empresa X',
            'cargo' => 'Assistente',
            'principais_atv' => 'Suporte',
            'data_inicio' => '2020-01-01',
            'data_fim' => '2022-01-01',
            'referencia_nome' => 'Chefe',
            'referencia_telefone' => '98900000000',
        ]);

        $arquivoId = DB::table('arquivos')->insertGetId([
            'nome' => 'doc.pdf',
            'extensao' => 'pdf',
            'bytes' => 500,
            'file' => 'doc-' . $empresaId . '.pdf',
            'disco' => 'local',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('documentos_curriculos')->insert([
            'curriculo_id' => $curriculoId,
            'arquivo_id' => $arquivoId,
            'tipo' => 'anexoscpfrg',
        ]);

        $feedbackId = DB::table('feedback_curriculos')->insertGetId([
            'curriculo_id' => $curriculoId,
            'cliente_id' => $clienteId,
            'empresa_id' => $empresaId,
            'vaga_id' => $vagaId,
            'vagas_abertas_id' => $vagaAbertaId,
            'status' => 'Em processo',
            'selecionado' => 'sim',
            'interesse' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'curriculo_id' => $curriculoId,
            'feedback_id' => $feedbackId,
            'vaga_id' => $vagaId,
        ];
    }
}
