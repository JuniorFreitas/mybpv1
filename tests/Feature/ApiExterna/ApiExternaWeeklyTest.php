<?php

namespace Tests\Feature\ApiExterna;

use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaScopes;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApiExternaSchema;
use Tests\TestCase;

class ApiExternaWeeklyTest extends TestCase
{
    use ApiExternaSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->garantirSchemaWeeklyApiExterna();
    }

    public function testWeeklySomenteLeituraDaEmpresa(): void
    {
        $empresaId = 99301;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::WEEKLY_READ]);
        $seed = $this->seedWeekly($empresaId);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);
        $auth = ['Authorization' => 'Bearer ' . $token];

        $quadro = $this->withHeaders($auth)
            ->getJson('/api/v3/weekly?q=Semanal')
            ->assertOk()
            ->json('data.0');

        $this->assertSame(ApiExternaId::encode($seed['quadro_id']), $quadro['public_id']);
        $this->assertSame('Quadro Semanal', $quadro['titulo']);
        $this->assertSame(1, $quadro['listas_count']);
        $this->assertSame(1, $quadro['tarefas_count']);
        $this->assertSame(2, $quadro['membros_count']);
        $this->assertArrayNotHasKey('id', $quadro);

        $detalhe = $this->withHeaders($auth)
            ->getJson('/api/v3/weekly/' . $quadro['public_id'])
            ->assertOk()
            ->json('data');
        $this->assertCount(2, $detalhe['membros']);
        $this->assertSame('Ana Dona', $detalhe['membros'][0]['nome']);
        $this->assertSame('dono', $detalhe['membros'][0]['papel']);
        $this->assertArrayNotHasKey('login', $detalhe['membros'][0]);
        $this->assertSame('A fazer', $detalhe['listas'][0]['titulo']);
        $tarefaNoQuadro = $detalhe['listas'][0]['tarefas'][0];
        $this->assertSame('@Ana revisar', $tarefaNoQuadro['descricao']);
        $this->assertSame('Bloqueio', $tarefaNoQuadro['comentarios'][0]['tipo_label']);
        $this->assertSame('Bloqueado no cliente', $tarefaNoQuadro['comentarios'][0]['texto']);
        $this->assertSame('Ana Dona', $tarefaNoQuadro['comentarios'][0]['autor']);
        $this->assertSame('Bruno Membro', $tarefaNoQuadro['checklists'][0]['itens'][0]['membros'][0]['nome']);
        $this->assertSame('1d', $tarefaNoQuadro['lembrete_texto']);
        $this->assertSame('moveu o card', $tarefaNoQuadro['logs'][0]['descricao']);
        $this->assertSame('criou o quadro', $detalhe['logs'][0]['descricao']);

        $listaPid = $detalhe['listas'][0]['public_id'];
        $tarefas = $this->withHeaders($auth)
            ->getJson('/api/v3/weekly/' . $quadro['public_id'] . '/listas/' . $listaPid . '/tarefas')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $tarefas);
        $this->assertSame('Revisar contrato', $tarefas[0]['titulo']);
        $this->assertTrue($tarefas[0]['em_atraso']);
        $this->assertSame('Bruno Membro', $tarefas[0]['membros'][0]['nome']);
        $this->assertSame('@Ana revisar', $tarefas[0]['descricao']);
        $this->assertSame('Bloqueado no cliente', $tarefas[0]['comentarios'][0]['texto']);
        $this->assertSame('contrato.pdf', $tarefas[0]['anexos'][0]['nome']);

        $tarefa = $this->withHeaders($auth)
            ->getJson('/api/v3/weekly/' . $quadro['public_id'] . '/tarefas/' . $tarefas[0]['public_id'])
            ->assertOk()
            ->json('data');
        $this->assertSame('@Ana revisar', $tarefa['descricao']);
        $this->assertStringNotContainsString('data-user-id', json_encode($tarefa));
        $this->assertSame('Documentos', $tarefa['checklists'][0]['titulo']);
        $this->assertTrue($tarefa['checklists'][0]['itens'][0]['concluido']);
        $this->assertSame('Bloqueado no cliente', $tarefa['comentarios'][0]['texto']);
        $this->assertSame('contrato.pdf', $tarefa['anexos'][0]['nome']);
        $this->assertArrayNotHasKey('file', $tarefa['anexos'][0]);
        $this->assertArrayNotHasKey('disco', $tarefa['anexos'][0]);

        $this->withHeaders($auth)
            ->getJson('/api/v3/weekly/' . ApiExternaId::encode($seed['outro_quadro_id']))
            ->assertNotFound();

        $this->withHeaders($auth)
            ->getJson('/api/v3/weekly?q=Arquivado')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    public function testWeeklyExigeScope(): void
    {
        $empresaId = 99302;
        $cred = $this->criarCredencialApiExterna($empresaId, [ApiExternaScopes::CLOUD_READ]);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/v3/weekly')
            ->assertForbidden();
    }

    /**
     * @return array{quadro_id: int, outro_quadro_id: int}
     */
    private function seedWeekly(int $empresaId): array
    {
        $donoId = DB::table('users')->insertGetId([
            'nome' => 'Ana Dona',
            'login' => 'secreto_login_dona',
            'password' => 'x',
            'empresa_id' => $empresaId,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $membroId = DB::table('users')->insertGetId([
            'nome' => 'Bruno Membro',
            'login' => 'secreto_login_membro',
            'password' => 'x',
            'empresa_id' => $empresaId,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $quadroId = DB::table('quadros')->insertGetId([
            'user_id' => $donoId,
            'titulo' => 'Quadro Semanal',
            'empresa_id' => $empresaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('quadros')->insert([
            'user_id' => $donoId,
            'titulo' => 'Quadro Arquivado',
            'empresa_id' => $empresaId,
            'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $outroQuadroId = DB::table('quadros')->insertGetId([
            'user_id' => $donoId,
            'titulo' => 'Quadro de outra empresa',
            'empresa_id' => $empresaId + 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('quadros_membros')->insert([
            [
                'quadro_id' => $quadroId,
                'user_id' => $donoId,
                'papel' => 'dono',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quadro_id' => $quadroId,
                'user_id' => $membroId,
                'papel' => 'membro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $listaId = DB::table('lista_tarefas')->insertGetId([
            'quadro_id' => $quadroId,
            'user_id' => $donoId,
            'titulo' => 'A fazer',
            'ordem' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tarefaId = DB::table('tarefas')->insertGetId([
            'lista_id' => $listaId,
            'user_id' => $donoId,
            'titulo' => 'Revisar contrato',
            'descricao' => '<p><span class="wr-mention" data-user-id="99" data-nome="Ana">@Ana</span> revisar</p>',
            'ordem' => 1,
            'concluido' => 0,
            'datahora_entrega' => '2020-01-01 10:00:00',
            'lembrete' => '2019-12-31 10:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('tarefas')->insert([
            'lista_id' => $listaId,
            'user_id' => $donoId,
            'titulo' => 'Tarefa excluída',
            'ordem' => 2,
            'concluido' => 0,
            'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('membros_tarefa')->insert([
            'tarefa_id' => $tarefaId,
            'user_id' => $membroId,
        ]);

        DB::table('log_weekly')->insert([
            [
                'quadro_id' => $quadroId,
                'tarefa_id' => null,
                'user_id' => $donoId,
                'descricao' => 'criou o quadro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quadro_id' => $quadroId,
                'tarefa_id' => $tarefaId,
                'user_id' => $membroId,
                'descricao' => 'moveu o card',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $checklistId = DB::table('checklists_tarefas')->insertGetId([
            'tarefa_id' => $tarefaId,
            'titulo' => 'Documentos',
            'ordem' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $itemId = DB::table('checklists_tarefa_items')->insertGetId([
            'checklist_id' => $checklistId,
            'titulo' => 'Contrato assinado',
            'concluido' => 1,
            'ordem' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('checklists_tarefa_items_membros')->insert([
            'checklists_tarefa_item_id' => $itemId,
            'user_id' => $membroId,
        ]);

        DB::table('tarefas_comentarios')->insert([
            'tarefa_id' => $tarefaId,
            'user_id' => $donoId,
            'comentario' => 'Bloqueado no cliente',
            'tipo' => 'bloqueio',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $arquivoId = DB::table('arquivos')->insertGetId([
            'nome' => 'contrato.pdf',
            'extensao' => 'pdf',
            'bytes' => 1200,
            'file' => 'segredo/contrato.pdf',
            'disco' => 'local',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('tarefa_anexos')->insert([
            'tarefa_id' => $tarefaId,
            'arquivo_id' => $arquivoId,
        ]);

        return [
            'quadro_id' => $quadroId,
            'outro_quadro_id' => $outroQuadroId,
        ];
    }
}
