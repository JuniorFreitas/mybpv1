<?php

namespace Tests\Feature\ApiExterna;

use App\Models\Admissao;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaScopes;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApiExternaSchema;
use Tests\TestCase;

class ApiExternaRelatorioTest extends TestCase
{
    use ApiExternaSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->garantirSchemaModulosExtrasApiExterna();
    }

    public function testCatalogoERelatoriosEfetivoAniversariantesCentroCusto(): void
    {
        $empresaId = 99201;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::RELATORIOS_READ]);
        $seed = $this->seedEfetivo($empresaId);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);
        $auth = ['Authorization' => 'Bearer ' . $token];

        $catalogo = $this->withHeaders($auth)
            ->getJson('/api/v3/relatorios')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->json('data');

        $this->assertCount(10, $catalogo);
        $slugs = collect($catalogo)->pluck('slug')->all();
        $this->assertContains('efetivo', $slugs);
        $this->assertContains('nps', $slugs);

        $efetivo = $this->withHeaders($auth)
            ->getJson('/api/v3/relatorios/efetivo')
            ->assertOk()
            ->json('data.0');

        $this->assertSame(ApiExternaId::encode($seed['feedback_id']), $efetivo['processo_public_id']);
        $this->assertSame('Ana Relatorio', $efetivo['nome']);
        $this->assertSame('***.***.***-88', $efetivo['cpf']);
        $this->assertArrayNotHasKey('empresa_id', $efetivo);
        $this->assertArrayHasKey('processo', $efetivo['links']);

        $cc = $this->withHeaders($auth)
            ->getJson('/api/v3/relatorios/centro-custo')
            ->assertOk()
            ->json('data.0');
        $this->assertSame(1, $cc['total']);
        $this->assertSame('CC Relatorio', $cc['label']);

        $mes = (int) date('n', strtotime($seed['nascimento']));
        $aniversariantes = $this->withHeaders($auth)
            ->getJson('/api/v3/relatorios/aniversariantes?mes=' . $mes)
            ->assertOk();
        $this->assertSame($mes, $aniversariantes->json('context.mes'));
        $this->assertSame('Ana Relatorio', $aniversariantes->json('data.0.nome'));

        $this->withHeaders($auth)
            ->getJson('/api/v3/relatorios/inexistente')
            ->assertNotFound();
    }

    public function testScopeObrigatorio(): void
    {
        $empresaId = 99202;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::PROCESSOS_READ]);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/relatorios')
            ->assertForbidden();
    }

    /**
     * @return array{feedback_id:int,nascimento:string}
     */
    private function seedEfetivo(int $empresaId): array
    {
        $nascimento = date('Y') . '-' . date('m') . '-15';

        $clienteId = DB::table('clientes')->insertGetId([
            'razao_social' => 'Empresa Relatorio',
            'nome' => 'Empresa Relatorio',
            'cnpj' => '33333333000133',
        ]);
        $centroId = DB::table('centro_custos')->insertGetId([
            'empresa_id' => $empresaId,
            'label' => 'CC Relatorio',
            'ativo' => true,
        ]);
        $curriculoId = DB::table('curriculos')->insertGetId([
            'nome' => 'Ana Relatorio',
            'cpf' => '98765432188',
            'email' => 'ana@example.com',
            'nascimento' => $nascimento,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $feedbackId = DB::table('feedback_curriculos')->insertGetId([
            'curriculo_id' => $curriculoId,
            'cliente_id' => $clienteId,
            'empresa_id' => $empresaId,
            'status' => 'Admitido',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('admissoes')->insert([
            'feedback_id' => $feedbackId,
            'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
            'data_admissao' => '2026-02-01',
            'cargo' => 'Analista',
            'centro_custo_id' => $centroId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'feedback_id' => $feedbackId,
            'nascimento' => $nascimento,
        ];
    }
}
