<?php

namespace Tests\Feature\ApiExterna;

use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaScopes;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApiExternaSchema;
use Tests\TestCase;

class ApiExternaAvaliacaoTest extends TestCase
{
    use ApiExternaSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->garantirSchemaAvaliacoesApiExterna();
    }

    public function testListagemEnriquecidaComTipoContagensELinks(): void
    {
        $empresaId = 98001;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::AVALIACOES_READ]);
        $seed = $this->seedAvaliacao($empresaId);

        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);

        $item = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v3/avaliacoes')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->json('data.0');

        $this->assertSame(ApiExternaId::encode($seed['avaliacao_id']), $item['public_id']);
        $this->assertArrayNotHasKey('empresa_id', $item);
        $this->assertArrayNotHasKey('id', $item);
        $this->assertSame('Desempenho 2026', $item['titulo']);
        $this->assertSame('Competências', $item['avaliacao_tipo']['nome']);
        $this->assertSame(2, $item['feedbacks_count']);
        $this->assertSame(1, $item['colaboradores_count']);
        $this->assertNotEmpty($item['fluxo_etapas']);
        $this->assertArrayHasKey('feedbacks', $item['links']);
    }

    public function testDetalheCatalogosFeedbacksResultadoEFluxo(): void
    {
        $empresaId = 98002;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::AVALIACOES_READ]);
        $seed = $this->seedAvaliacao($empresaId);
        $avaliacaoPid = ApiExternaId::encode($seed['avaliacao_id']);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);
        $auth = ['Authorization' => 'Bearer ' . $token];

        $detalhe = $this->withHeaders($auth)
            ->getJson('/api/v3/avaliacoes/' . $avaliacaoPid)
            ->assertOk()
            ->json('data');

        $this->assertArrayHasKey('fluxo', $detalhe);
        $this->assertSame('Competências', $detalhe['avaliacao_tipo']['nome']);
        $this->assertStringContainsString('Auto Avaliação', $detalhe['fluxo_etapas'][0]['label']);

        $tipos = $this->withHeaders($auth)
            ->getJson('/api/v3/avaliacoes/tipos')
            ->assertOk()
            ->json('data');
        $this->assertSame('Competências', $tipos[0]['nome']);

        $tiposAval = $this->withHeaders($auth)
            ->getJson('/api/v3/avaliacoes/tipos-avaliador')
            ->assertOk()
            ->json('data');
        $this->assertSame('Gestor', $tiposAval[0]['label']);

        $topicos = $this->withHeaders($auth)
            ->getJson('/api/v3/avaliacoes/tipos/' . ApiExternaId::encode($seed['tipo_id']) . '/topicos')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $topicos['topicos']);
        $this->assertSame('Comunicação', $topicos['topicos'][0]['topico']);
        $this->assertSame('Clareza', $topicos['topicos'][0]['subtopicos'][0]['topico']);

        $feedbacks = $this->withHeaders($auth)
            ->getJson('/api/v3/avaliacoes/' . $avaliacaoPid . '/feedbacks')
            ->assertOk()
            ->json('data');
        $this->assertCount(2, $feedbacks);
        $this->assertSame('Colaborador Teste', $feedbacks[0]['funcionario']['nome']);
        $this->assertArrayNotHasKey('comentario', $feedbacks[0]);
        $this->assertArrayHasKey('resultado', $feedbacks[0]['links']);

        $feedbackPid = collect($feedbacks)->firstWhere('principal', true)['public_id']
            ?? $feedbacks[0]['public_id'];

        $feedback = $this->withHeaders($auth)
            ->getJson('/api/v3/avaliacoes/' . $avaliacaoPid . '/feedbacks/' . $feedbackPid)
            ->assertOk()
            ->json('data');
        $this->assertSame('Bom desempenho', $feedback['comentario']);
        $this->assertNotEmpty($feedback['respostas']);
        $this->assertSame(4, $feedback['respostas'][0]['nota']);
        $this->assertTrue($feedback['pdi_cadastrado']);

        $resultado = $this->withHeaders($auth)
            ->getJson('/api/v3/avaliacoes/' . $avaliacaoPid . '/feedbacks/' . $feedbackPid . '/resultado')
            ->assertOk()
            ->json('data');
        $this->assertSame(2, $resultado['total_etapas']);
        $this->assertNotEmpty($resultado['medias_por_topico']);
        $this->assertSame('Melhorar feedbacks', $resultado['planos_acao'][0]['plano_de_acao']);

        $fluxo = $this->withHeaders($auth)
            ->getJson(
                '/api/v3/avaliacoes/' . $avaliacaoPid
                . '/colaboradores/' . ApiExternaId::encode($seed['funcionario_id'])
                . '/feedbacks'
            )
            ->assertOk()
            ->json('data');
        $this->assertCount(2, $fluxo['feedbacks']);

        $filtrado = $this->withHeaders($auth)
            ->getJson('/api/v3/avaliacoes?ano_avaliacao=2026&q=Desempenho')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $filtrado);
    }

    /**
     * @return array{avaliacao_id:int,tipo_id:int,funcionario_id:int,feedback_principal_id:int}
     */
    private function seedAvaliacao(int $empresaId): array
    {
        $funcionarioId = DB::table('users')->insertGetId([
            'nome' => 'Colaborador Teste',
            'login' => 'colab_' . $empresaId,
            'password' => 'x',
            'empresa_id' => $empresaId,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gestorId = DB::table('users')->insertGetId([
            'nome' => 'Gestor Teste',
            'login' => 'gestor_' . $empresaId,
            'password' => 'x',
            'empresa_id' => $empresaId,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tipoId = DB::table('avaliacoes_tipos')->insertGetId([
            'empresa_id' => $empresaId,
            'nome' => 'Competências',
            'descricao' => 'Tipo padrão',
            'ativo' => true,
            'tipo_pj' => false,
        ]);

        $tipoAvaliadorId = DB::table('avaliacao_avaliadores_tipos')->insertGetId([
            'empresa_id' => $empresaId,
            'label' => 'Gestor',
            'descricao' => 'Avaliador final',
            'ativo' => true,
            'tipo_pj' => false,
        ]);

        $topicoPaiId = DB::table('avaliacoes_topicos')->insertGetId([
            'empresa_id' => $empresaId,
            'avaliacao_tipo_id' => $tipoId,
            'topico_pai_id' => null,
            'topico' => 'Comunicação',
            'topico_explicacao' => 'Pai',
            'ativo' => true,
            'tipo_pj' => false,
        ]);

        $topicoFilhoId = DB::table('avaliacoes_topicos')->insertGetId([
            'empresa_id' => $empresaId,
            'avaliacao_tipo_id' => $tipoId,
            'topico_pai_id' => $topicoPaiId,
            'topico' => 'Clareza',
            'topico_explicacao' => 'Filho',
            'ativo' => true,
            'tipo_pj' => false,
        ]);

        $avaliacaoId = DB::table('avaliacoes')->insertGetId([
            'empresa_id' => $empresaId,
            'avaliacao_tipo_id' => $tipoId,
            'titulo' => 'Desempenho 2026',
            'ano_avaliacao' => 2026,
            'data_inicio_prazo' => '2026-01-01',
            'data_fim_prazo' => '2026-12-31',
            'status' => 'Aberta',
            'ativo' => true,
            'auto_avaliacao' => true,
            'fluxo' => json_encode([
                ['id' => $tipoAvaliadorId, 'label' => 'Gestor', 'principal' => true],
            ]),
            'tipo_pj' => false,
            'mostrar_notas_avaliador_final' => true,
        ]);

        $autoId = DB::table('avaliacoes_feedbacks')->insertGetId([
            'avaliacao_id' => $avaliacaoId,
            'empresa_id' => $empresaId,
            'funcionario_id' => $funcionarioId,
            'avaliador_id' => $funcionarioId,
            'avaliacao_tipo_id' => null,
            'origem_feedback' => 'Funcionario',
            'principal' => false,
            'nota_final_total' => null,
            'comentario' => 'Auto ok',
            'status' => 'Avaliada',
            'tipo_pj' => false,
        ]);

        $principalId = DB::table('avaliacoes_feedbacks')->insertGetId([
            'avaliacao_id' => $avaliacaoId,
            'empresa_id' => $empresaId,
            'funcionario_id' => $funcionarioId,
            'avaliador_id' => $gestorId,
            'avaliacao_tipo_id' => $tipoAvaliadorId,
            'origem_feedback' => 'Avaliador',
            'principal' => true,
            'nota_final_total' => 4,
            'comentario' => 'Bom desempenho',
            'status' => 'Finalizada',
            'tipo_pj' => false,
        ]);

        DB::table('avaliacoes_respostas')->insert([
            [
                'empresa_id' => $empresaId,
                'avaliacao_feedback_id' => $autoId,
                'topico_id' => $topicoFilhoId,
                'nota' => 5,
            ],
            [
                'empresa_id' => $empresaId,
                'avaliacao_feedback_id' => $principalId,
                'topico_id' => $topicoFilhoId,
                'nota' => 4,
            ],
        ]);

        DB::table('avaliacoes_resultados')->insert([
            'avaliacao_feedback_id' => $principalId,
            'gestor_id' => $gestorId,
            'topico_id' => $topicoFilhoId,
            'empresa_id' => $empresaId,
            'plano_de_acao' => 'Melhorar feedbacks',
            'responsavel' => 'Gestor Teste',
            'inicio' => '2026-02-01',
            'termino' => '2026-03-01',
            'status' => 'Em andamento',
            'tipo_pj' => false,
        ]);

        return [
            'avaliacao_id' => $avaliacaoId,
            'tipo_id' => $tipoId,
            'funcionario_id' => $funcionarioId,
            'feedback_principal_id' => $principalId,
        ];
    }
}
