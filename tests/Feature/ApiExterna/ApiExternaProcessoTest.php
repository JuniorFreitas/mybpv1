<?php

namespace Tests\Feature\ApiExterna;

use App\Models\Admissao;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaScopes;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApiExternaSchema;
use Tests\TestCase;

class ApiExternaProcessoTest extends TestCase
{
    use ApiExternaSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->garantirSchemaProcessosApiExterna();
    }

    public function testProcessoAtivoRetornaPayloadEnriquecidoDaAdmissao(): void
    {
        $empresaId = 95001;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::PROCESSOS_READ]);
        $seed = $this->seedProcessoAdmitido($empresaId);

        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/processos/ativos')
            ->assertOk()
            ->assertJsonPath('success', true);

        $item = $response->json('data.0');
        $this->assertNotNull($item);
        $this->assertSame(ApiExternaId::encode($seed['feedback_id']), $item['public_id']);
        $this->assertArrayNotHasKey('empresa_id', $item);
        $this->assertArrayNotHasKey('id', $item);

        $this->assertArrayHasKey('curriculo', $item);
        $this->assertSame('Maria Silva', $item['curriculo']['nome']);
        $this->assertSame('***.***.***-99', $item['curriculo']['cpf']);

        $this->assertArrayHasKey('admissao', $item);
        $this->assertSame(Admissao::STATUS_ADMISSAO_ADMITIDO, $item['admissao']['status']);
        $this->assertSame('Operador', $item['admissao']['cargo']);

        $this->assertArrayHasKey('parecer_rh', $item);
        $this->assertArrayHasKey('resultado_integrado', $item);
        $this->assertArrayHasKey('vaga_aberta', $item);
        $this->assertArrayHasKey('empresa', $item);
        $this->assertArrayNotHasKey('cliente', $item);
        $this->assertArrayNotHasKey('cliente_public_id', $item);
        $this->assertSame(ApiExternaId::encode($seed['cliente_id']), $item['empresa']['public_id']);
        $this->assertArrayHasKey('conta_banco', $item);
        $this->assertSame('***pix9', $item['conta_banco']['chavepix']);
        $this->assertArrayHasKey('telefone_principal', $item);
        $this->assertSame('98999998888', $item['telefone_principal']['numero']);
        $this->assertArrayHasKey('self', $item['links']);
        $this->assertArrayHasKey('etapas', $item['links']);
    }

    public function testProcessoDetalheRetornaCamposCompletos(): void
    {
        $empresaId = 95002;
        $cred = $this->criarCredencialApiExterna($empresaId, [
            ApiExternaScopes::PROCESSOS_READ,
            ApiExternaScopes::FLUXO_READ,
        ]);
        $seed = $this->seedProcessoAdmitido($empresaId);
        $publicId = ApiExternaId::encode($seed['feedback_id']);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);

        $detalhe = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/processos/' . $publicId)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->json('data');

        $this->assertArrayHasKey('pre_admissao', $detalhe);
        $this->assertArrayHasKey('dependentes', $detalhe['curriculo']);
        $this->assertArrayHasKey('telefones', $detalhe['curriculo']);
        $this->assertArrayHasKey('ferias_adquiridas', $detalhe['admissao']);
        $this->assertArrayHasKey('dados_admissao', $detalhe['admissao']);
        $this->assertArrayHasKey('exames', $detalhe['links']);
        $this->assertArrayHasKey('fluxo', $detalhe['links']);

        $fluxo = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/fluxo/' . $publicId)
            ->assertOk()
            ->json('data');

        $this->assertTrue($fluxo['etapas']['admissao']);
        $this->assertTrue($fluxo['etapas']['pos_admissao']);
        $this->assertSame('Maria Silva', $fluxo['curriculo']['nome']);
    }

    public function testFiltrosBuscarDemitidosENested(): void
    {
        $empresaId = 95003;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::PROCESSOS_READ]);
        $seed = $this->seedProcessoAdmitido($empresaId);
        $demitidoId = $this->seedProcessoDemitido($empresaId);
        $publicId = ApiExternaId::encode($seed['feedback_id']);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);
        $auth = ['Authorization' => 'Bearer ' . $token];

        $filtrado = $this->withHeaders($auth)
            ->getJson('/api/v3/processos/ativos?q=Maria&uf=MA')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $filtrado);
        $this->assertSame($publicId, $filtrado[0]['public_id']);

        $porEmpresa = $this->withHeaders($auth)
            ->getJson('/api/v3/processos/ativos?empresa_public_id=' . ApiExternaId::encode($seed['cliente_id']))
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $porEmpresa);

        $busca = $this->withHeaders($auth)
            ->getJson('/api/v3/processos/buscar?q=Maria')
            ->assertOk()
            ->json('data');
        $this->assertGreaterThanOrEqual(1, count($busca));

        $this->withHeaders($auth)
            ->getJson('/api/v3/processos/buscar?q=x')
            ->assertStatus(422);

        $demitidos = $this->withHeaders($auth)
            ->getJson('/api/v3/processos/demitidos')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $demitidos);
        $this->assertSame(ApiExternaId::encode($demitidoId), $demitidos[0]['public_id']);

        $etapas = $this->withHeaders($auth)
            ->getJson('/api/v3/processos/' . $publicId . '/etapas')
            ->assertOk()
            ->json('data');
        $this->assertTrue($etapas['etapas']['admissao']);
        $this->assertTrue($etapas['etapas']['pos_admissao']);
        $this->assertFalse($etapas['etapas']['demitido']);

        $exames = $this->withHeaders($auth)
            ->getJson('/api/v3/processos/' . $publicId . '/exames')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $exames['exames']);
        $this->assertTrue($exames['exames'][0]['exame_realizado']);

        $docs = $this->withHeaders($auth)
            ->getJson('/api/v3/processos/' . $publicId . '/documentos')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $docs['documentos']);
        $this->assertSame('anexoscpfrg', $docs['documentos'][0]['tipo']);
        $this->assertArrayNotHasKey('file', $docs['documentos'][0]['arquivos'][0]);

        $ferias = $this->withHeaders($auth)
            ->getJson('/api/v3/processos/' . $publicId . '/ferias')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $ferias['ferias_adquiridas']);
        $this->assertSame(15, $ferias['ferias_adquiridas'][0]['qnt_dias']);

        $pre = $this->withHeaders($auth)
            ->getJson('/api/v3/processos/' . $publicId . '/pre-admissao')
            ->assertOk()
            ->json('data');
        $this->assertNotNull($pre['pre_admissao']);
        $this->assertArrayHasKey('documentos', $pre['links']);
    }

    /**
     * @return array{feedback_id:int,cliente_id:int,curriculo_id:int,admissao_id:int}
     */
    private function seedProcessoAdmitido(int $empresaId): array
    {
        $areaId = DB::table('areas')->insertGetId(['label' => 'Operações', 'ativo' => true]);
        $clienteId = DB::table('clientes')->insertGetId([
            'razao_social' => 'Cliente API LTDA',
            'nome' => 'Cliente API',
            'cnpj' => '12345678000199',
            'area_id' => $areaId,
        ]);
        $municipioId = DB::table('municipios')->insertGetId(['nome' => 'São Luís', 'uf' => 'MA']);
        $vagaId = DB::table('vagas')->insertGetId([
            'nome' => 'Operador',
            'ativo' => true,
            'empresa_id' => $empresaId,
        ]);
        $vagaAbertaId = DB::table('vagas_abertas')->insertGetId([
            'empresa_id' => $empresaId,
            'vaga_id' => $vagaId,
            'titulo' => 'Operador - São Luís',
            'municipio_id' => $municipioId,
            'ativo' => true,
        ]);
        $curriculoId = DB::table('curriculos')->insertGetId([
            'nome' => 'Maria Silva',
            'cpf' => '12345678999',
            'email' => 'maria@example.com',
            'sexo' => 'F',
            'municipio' => 'São Luís',
            'uf' => 'MA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $telId = DB::table('curriculo_telefone')->insertGetId([
            'curriculo_id' => $curriculoId,
            'numero' => '98999998888',
            'pais' => '55',
            'tipo' => 'whatsapp',
            'principal' => true,
        ]);
        DB::table('usuario_contas')->insert([
            'user_id' => $curriculoId,
            'banco' => '001',
            'agencia' => '1234',
            'conta' => '56789-0',
            'pix' => true,
            'tipochavepix' => 'aleatoria',
            'chavepix' => 'meupix9',
        ]);

        $feedbackId = DB::table('feedback_curriculos')->insertGetId([
            'curriculo_id' => $curriculoId,
            'cliente_id' => $clienteId,
            'empresa_id' => $empresaId,
            'vaga_id' => $vagaId,
            'vagas_abertas_id' => $vagaAbertaId,
            'telefone_id' => $telId,
            'status' => 'Admitido',
            'selecionado' => 'sim',
            'interesse' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('resultado_integrados')->insert([
            'feedback_id' => $feedbackId,
            'documentos_entregue' => true,
            'encaminhado_exame' => true,
            'encaminhado_treinamento' => true,
            'responsavel_envio' => 'RH',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('parecer_rh')->insert([
            'feedback_id' => $feedbackId,
            'indicado_por' => 'João',
            'calca' => 42,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admissaoId = DB::table('admissoes')->insertGetId([
            'feedback_id' => $feedbackId,
            'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
            'data_admissao' => '2026-01-15',
            'cargo' => 'Operador',
            'funcao' => 'Operação',
            'salario' => 2500.50,
            'pis' => '12345678901',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('feedback_preadmissao')->insert([
            'feedback_id' => $feedbackId,
            'user_finalizou_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('examesesmts')->insert([
            'feedback_id' => $feedbackId,
            'exame_realizado' => true,
            'atual' => true,
            'resultado' => json_encode(['aprovado' => 'Sim']),
            'data_realizacao' => '2026-01-10',
            'data_vencimento' => '2027-01-10',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ferias_adquiridas')->insert([
            'admissao_id' => $admissaoId,
            'periodo_gozado' => '2026',
            'qnt_dias' => 15,
            'data_saida' => '2026-07-01',
            'data_retorno' => '2026-07-16',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $arquivoId = DB::table('arquivos')->insertGetId([
            'nome' => 'rg-cpf.pdf',
            'extensao' => 'pdf',
            'bytes' => 1024,
            'file' => 'rg-' . $empresaId . '.pdf',
            'disco' => 'local',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('documentos_curriculos')->insert([
            'curriculo_id' => $curriculoId,
            'arquivo_id' => $arquivoId,
            'tipo' => 'anexoscpfrg',
        ]);

        return [
            'feedback_id' => $feedbackId,
            'cliente_id' => $clienteId,
            'curriculo_id' => $curriculoId,
            'admissao_id' => $admissaoId,
        ];
    }

    private function seedProcessoDemitido(int $empresaId): int
    {
        $areaId = DB::table('areas')->insertGetId(['label' => 'Logística', 'ativo' => true]);
        $clienteId = DB::table('clientes')->insertGetId([
            'razao_social' => 'Cliente Demitido LTDA',
            'nome' => 'Cliente Dem',
            'cnpj' => '99888777000155',
            'area_id' => $areaId,
        ]);
        $curriculoId = DB::table('curriculos')->insertGetId([
            'nome' => 'Pedro Demitido',
            'cpf' => '98765432100',
            'email' => 'pedro@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $feedbackId = DB::table('feedback_curriculos')->insertGetId([
            'curriculo_id' => $curriculoId,
            'cliente_id' => $clienteId,
            'empresa_id' => $empresaId,
            'status' => 'Demitido',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('admissoes')->insert([
            'feedback_id' => $feedbackId,
            'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
            'data_admissao' => '2025-01-01',
            'cargo' => 'Auxiliar',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('demissaos')->insert([
            'feedback_id' => $feedbackId,
            'data_desmobilizacao' => '2026-03-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $feedbackId;
    }
}
