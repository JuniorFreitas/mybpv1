<?php

namespace Tests\Feature;

use App\Models\ListaTarefa;
use App\Models\Quadro;
use App\Models\QuadroMembro;
use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WeeklyReportMultitenantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);
        $this->withoutMiddleware([
            \App\Http\Middleware\CarregaHabilidades::class,
            \App\Http\Middleware\CheckPasswordReset::class,
        ]);

        Gate::define('weekly_report', fn () => true);
        Gate::define('weekly_report_quadro_insert', fn () => true);
        Gate::define('weekly_report_quadro_update', fn () => true);
        Gate::define('weekly_report_quadro_delete', fn () => true);
        Gate::define('weekly_report_quadro_lista_insert', fn () => true);
        Gate::define('weekly_report_quadro_lista_update', fn () => true);
        Gate::define('weekly_report_quadro_lista_delete', fn () => true);
        Gate::define('weekly_report_quadro_tarefa_insert', fn () => true);
        Gate::define('weekly_report_quadro_tarefa_update', fn () => true);
        Gate::define('weekly_report_quadro_tarefa_delete', fn () => true);

        Schema::dropIfExists('tarefa_anexos');
        Schema::dropIfExists('tarefas_comentarios');
        Schema::dropIfExists('membros_tarefa');
        Schema::dropIfExists('checklists_tarefa_items_membros');
        Schema::dropIfExists('checklists_tarefa_items');
        Schema::dropIfExists('checklists_tarefas');
        Schema::dropIfExists('log_weekly');
        Schema::dropIfExists('tarefas');
        Schema::dropIfExists('lista_tarefas');
        Schema::dropIfExists('quadros_membros');
        Schema::dropIfExists('quadros');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('login')->nullable();
            $table->string('tipo')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('require_password_reset')->default(false);
            $table->integer('password_reset_days')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('quadros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('quem_deletou_id')->nullable();
            $table->text('titulo');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('quadros_membros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quadro_id');
            $table->unsignedBigInteger('user_id');
            $table->string('papel', 20)->default('membro');
            $table->timestamps();
            $table->unique(['quadro_id', 'user_id']);
        });

        Schema::create('lista_tarefas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quadro_id');
            $table->unsignedBigInteger('user_id');
            $table->text('titulo');
            $table->integer('ordem')->default(1);
            $table->timestamps();
        });

        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lista_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('quem_deletou_id')->nullable();
            $table->text('titulo');
            $table->text('descricao')->nullable();
            $table->integer('ordem')->default(1);
            $table->dateTime('datahora_inicio')->nullable();
            $table->dateTime('datahora_entrega')->nullable();
            $table->dateTime('lembrete')->nullable();
            $table->boolean('concluido')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('log_weekly', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quadro_id');
            $table->unsignedBigInteger('tarefa_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('descricao');
            $table->timestamps();
        });

        Schema::create('membros_tarefa', function (Blueprint $table) {
            $table->unsignedBigInteger('tarefa_id');
            $table->unsignedBigInteger('user_id');
        });

        Schema::create('checklists_tarefas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tarefa_id');
            $table->text('titulo');
            $table->integer('ordem')->default(1);
            $table->dateTime('datahora_entrega')->nullable();
            $table->timestamps();
        });

        Schema::create('checklists_tarefa_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checklist_id');
            $table->text('titulo');
            $table->boolean('concluido')->default(false);
            $table->integer('ordem')->default(1);
            $table->dateTime('datahora_entrega')->nullable();
            $table->timestamps();
        });

        Schema::create('checklists_tarefa_items_membros', function (Blueprint $table) {
            $table->unsignedBigInteger('checklists_tarefa_item_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['checklists_tarefa_item_id', 'user_id'], 'ck_items_membros_pk');
        });

        Schema::create('tarefas_comentarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tarefa_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comentario');
            $table->string('tipo', 20)->default('comentario');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tarefa_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('tarefa_id');
            $table->unsignedBigInteger('arquivo_id');
        });

        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('file')->nullable();
            $table->string('disco')->nullable();
            $table->timestamps();
        });

        // Não usar Event::fake() global: desliga Eloquent model events (ex.: pivot dono no Quadro::created)
        Event::fake([
            \App\Events\WeeklyReport\QuadroEvent::class,
            \App\Events\WeeklyReport\ListaEvent::class,
            \App\Events\WeeklyReport\TarefaEvent::class,
            \App\Events\WeeklyReport\CheckListTarefaEvent::class,
            \App\Events\WeeklyReport\ItemChecklistEvent::class,
            \App\Events\WeeklyReport\ComentarioTarefaEvent::class,
            \App\Events\WeeklyReport\LogWeeklyEvent::class,
            \App\Events\WeeklyReport\AnexoEvent::class,
            \App\Events\Notificacoes\NotificacaoEvent::class,
        ]);
    }

    private function user(int $empresaId = 10, string $nome = null): User
    {
        static $seq = 0;
        $seq++;

        return User::query()->create([
            'nome' => $nome ?? ('User ' . $empresaId . ' #' . $seq),
            'login' => "user{$empresaId}_{$seq}@test.com",
            'tipo' => User::ADMINISTRADOR,
            'empresa_id' => $empresaId,
            'ativo' => true,
        ]);
    }

    private function addMembro(Quadro $quadro, User $user, string $papel = QuadroMembro::PAPEL_MEMBRO): void
    {
        QuadroMembro::query()->firstOrCreate(
            ['quadro_id' => $quadro->id, 'user_id' => $user->id],
            ['papel' => $papel]
        );
    }

    public function test_lista_quadros_somente_do_tenant(): void
    {
        $userA = $this->user(10);
        $userB = $this->user(99);

        $this->actingAs($userA);
        Quadro::query()->create(['titulo' => 'A', 'empresa_id' => 10, 'user_id' => $userA->id]);

        $this->actingAs($userB);
        Quadro::query()->create(['titulo' => 'B', 'empresa_id' => 99, 'user_id' => $userB->id]);

        $this->actingAs($userA);
        $response = $this->getJson('/g/weekly-report/10');
        $response->assertOk();
        $titulos = collect($response->json('lista'))->pluck('titulo');
        $this->assertTrue($titulos->contains('A'));
        $this->assertFalse($titulos->contains('B'));
        $this->assertTrue($response->json('lista_insert'));
    }

    public function test_recusa_acesso_com_empresa_id_diferente(): void
    {
        $user = $this->user(10);
        $this->actingAs($user);

        $this->getJson('/g/weekly-report/99')->assertStatus(403);
    }

    public function test_cria_lista_e_tarefa_no_quadro_do_tenant(): void
    {
        $user = $this->user(10);
        $this->actingAs($user);

        $quadro = Quadro::query()->create([
            'titulo' => 'Board',
            'empresa_id' => 10,
            'user_id' => $user->id,
        ]);

        $response = $this->postJson("/g/weekly-report/10/quadros/{$quadro->id}/listas", [
            'titulo' => 'A fazer',
        ]);
        $response->assertCreated();

        $lista = ListaTarefa::query()->where('quadro_id', $quadro->id)->first();
        $this->assertNotNull($lista);

        $this->postJson("/g/weekly-report/10/quadros/{$quadro->id}/listas/{$lista->id}/tarefas", [
            'titulo' => 'Card 1',
        ])->assertCreated();

        $this->assertDatabaseHas('tarefas', [
            'lista_id' => $lista->id,
            'titulo' => 'Card 1',
        ]);
    }

    public function test_nao_acessa_lista_de_outro_quadro(): void
    {
        $user = $this->user(10);
        $this->actingAs($user);

        $quadroA = Quadro::query()->create(['titulo' => 'A', 'empresa_id' => 10, 'user_id' => $user->id]);
        $quadroB = Quadro::query()->create(['titulo' => 'B', 'empresa_id' => 10, 'user_id' => $user->id]);
        $listaB = ListaTarefa::query()->create([
            'titulo' => 'Lista B',
            'quadro_id' => $quadroB->id,
            'user_id' => $user->id,
            'ordem' => 1,
        ]);

        $this->putJson("/g/weekly-report/10/quadros/{$quadroA->id}/listas/{$listaB->id}", [
            'titulo' => 'Hack',
        ])->assertStatus(404);
    }

    public function test_move_tarefa_entre_listas(): void
    {
        $user = $this->user(10);
        $this->actingAs($user);

        $quadro = Quadro::query()->create(['titulo' => 'Board', 'empresa_id' => 10, 'user_id' => $user->id]);
        $lista1 = ListaTarefa::query()->create(['titulo' => 'L1', 'quadro_id' => $quadro->id, 'user_id' => $user->id, 'ordem' => 1]);
        $lista2 = ListaTarefa::query()->create(['titulo' => 'L2', 'quadro_id' => $quadro->id, 'user_id' => $user->id, 'ordem' => 2]);
        $tarefa = Tarefa::query()->create([
            'titulo' => 'Mover',
            'lista_id' => $lista1->id,
            'user_id' => $user->id,
            'ordem' => 1,
        ]);

        $response = $this->putJson("/g/weekly-report/10/quadros/{$quadro->id}/listas/{$lista2->id}/tarefas", [
            'evento' => 'adicionar',
            'tarefa_id' => $tarefa->id,
            'novaLista' => [
                ['id' => $tarefa->id, 'lista_id' => $lista2->id, 'ordem' => 1],
            ],
        ]);
        $response->assertOk();

        $this->assertDatabaseHas('tarefas', [
            'id' => $tarefa->id,
            'lista_id' => $lista2->id,
            'ordem' => 1,
        ]);
    }

    public function test_adiciona_membro_no_item_do_checklist(): void
    {
        $user = $this->user(10);
        $membro = $this->user(10, 'Membro Item');
        $this->actingAs($user);

        $quadro = Quadro::query()->create(['titulo' => 'Board', 'empresa_id' => 10, 'user_id' => $user->id]);
        $this->addMembro($quadro, $membro);
        $lista = ListaTarefa::query()->create(['titulo' => 'L1', 'quadro_id' => $quadro->id, 'user_id' => $user->id, 'ordem' => 1]);
        $tarefa = Tarefa::query()->create([
            'titulo' => 'Card',
            'lista_id' => $lista->id,
            'user_id' => $user->id,
            'ordem' => 1,
        ]);
        $checklist = \App\Models\ChecklistsTarefa::query()->create([
            'tarefa_id' => $tarefa->id,
            'titulo' => 'CK',
            'ordem' => 1,
        ]);
        $item = \App\Models\ChecklistsTarefaItem::query()->create([
            'checklist_id' => $checklist->id,
            'titulo' => 'Item 1',
            'concluido' => false,
            'ordem' => 1,
        ]);

        $this->putJson(
            "/g/weekly-report/10/quadros/{$quadro->id}/listas/{$lista->id}/tarefas/{$tarefa->id}/checklist/{$checklist->id}/item/{$item->id}/updateMembro",
            ['acao' => 'add', 'user_id' => $membro->id]
        )->assertOk();

        $this->assertDatabaseHas('checklists_tarefa_items_membros', [
            'checklists_tarefa_item_id' => $item->id,
            'user_id' => $membro->id,
        ]);

        $foraDoQuadro = $this->user(10, 'Fora do Quadro');
        $this->putJson(
            "/g/weekly-report/10/quadros/{$quadro->id}/listas/{$lista->id}/tarefas/{$tarefa->id}/checklist/{$checklist->id}/item/{$item->id}/updateMembro",
            ['acao' => 'add', 'user_id' => $foraDoQuadro->id]
        )->assertStatus(400);

        $outroTenant = $this->user(99, 'Outro');
        $this->putJson(
            "/g/weekly-report/10/quadros/{$quadro->id}/listas/{$lista->id}/tarefas/{$tarefa->id}/checklist/{$checklist->id}/item/{$item->id}/updateMembro",
            ['acao' => 'add', 'user_id' => $outroTenant->id]
        )->assertStatus(400);
    }

    public function test_sanitiza_mencao_em_html_do_weekly_report(): void
    {
        $html = '<p>Olá <span class="wr-mention" contenteditable="false" data-user-id="12" data-nome="Ana Silva" onclick="alert(1)">@Ana Silva</span> <script>x</script></p>';
        $clean = \App\Support\WeeklyReportHtml::sanitize($html);

        $this->assertStringContainsString('class="wr-mention"', $clean);
        $this->assertStringContainsString('data-user-id="12"', $clean);
        $this->assertStringContainsString('@Ana Silva', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertSame([12], \App\Support\WeeklyReportHtml::extractMentionUserIds($clean));
    }

    public function test_mencao_na_descricao_adiciona_membro_na_tarefa(): void
    {
        Queue::fake([\App\Jobs\Weekly_report\UpdateMembrosJob::class]);

        $user = $this->user(10);
        $mencionado = $this->user(10, 'Ana Mencionada');
        $this->actingAs($user);

        $quadro = Quadro::query()->create(['titulo' => 'Board', 'empresa_id' => 10, 'user_id' => $user->id]);
        $this->addMembro($quadro, $mencionado);
        $lista = ListaTarefa::query()->create(['titulo' => 'L1', 'quadro_id' => $quadro->id, 'user_id' => $user->id, 'ordem' => 1]);
        $tarefa = Tarefa::query()->create([
            'titulo' => 'Card',
            'lista_id' => $lista->id,
            'user_id' => $user->id,
            'ordem' => 1,
            'descricao' => '',
        ]);

        $html = '<p>Oi <span class="wr-mention" contenteditable="false" data-user-id="' . $mencionado->id . '" data-nome="Ana Mencionada">@Ana Mencionada</span></p>';

        $this->putJson("/g/weekly-report/10/quadros/{$quadro->id}/listas/{$lista->id}/tarefas/{$tarefa->id}", [
            'descricao' => $html,
        ])->assertOk();

        $this->assertDatabaseHas('membros_tarefa', [
            'tarefa_id' => $tarefa->id,
            'user_id' => $mencionado->id,
        ]);
    }

    public function test_listagem_so_quadros_com_membership(): void
    {
        $dono = $this->user(10, 'Dono');
        $outro = $this->user(10, 'Outro User');

        $this->actingAs($dono);
        $meu = Quadro::query()->create(['titulo' => 'Meu', 'empresa_id' => 10, 'user_id' => $dono->id]);

        $this->actingAs($outro);
        $alheio = Quadro::query()->create(['titulo' => 'Alheio', 'empresa_id' => 10, 'user_id' => $outro->id]);

        $this->actingAs($dono);
        $response = $this->getJson('/g/weekly-report/10');
        $response->assertOk();
        $titulos = collect($response->json('lista'))->pluck('titulo');
        $this->assertTrue($titulos->contains('Meu'));
        $this->assertFalse($titulos->contains('Alheio'));
        $this->assertTrue(collect($response->json('lista'))->firstWhere('id', $meu->id)['sou_dono']);
        $this->assertDatabaseHas('quadros_membros', [
            'quadro_id' => $meu->id,
            'user_id' => $dono->id,
            'papel' => QuadroMembro::PAPEL_DONO,
        ]);
        $this->assertDatabaseMissing('quadros_membros', [
            'quadro_id' => $alheio->id,
            'user_id' => $dono->id,
        ]);
    }

    public function test_nao_membro_recebe_403_nas_rotas_do_quadro(): void
    {
        $dono = $this->user(10, 'Dono');
        $intruso = $this->user(10, 'Intruso');
        $this->actingAs($dono);
        $quadro = Quadro::query()->create(['titulo' => 'Privado', 'empresa_id' => 10, 'user_id' => $dono->id]);
        $lista = ListaTarefa::query()->create([
            'titulo' => 'L1',
            'quadro_id' => $quadro->id,
            'user_id' => $dono->id,
            'ordem' => 1,
        ]);

        $this->actingAs($intruso);
        $this->postJson("/g/weekly-report/10/quadros/{$quadro->id}/listas", ['titulo' => 'Hack'])
            ->assertStatus(403);
        $this->getJson("/g/weekly-report/10/quadros/{$quadro->id}/membros")
            ->assertStatus(403);
        $this->putJson("/g/weekly-report/10/quadros/{$quadro->id}/listas/{$lista->id}", ['titulo' => 'X'])
            ->assertStatus(403);
    }

    public function test_dono_compartilha_e_remove_membro(): void
    {
        $dono = $this->user(10, 'Dono Share');
        $convidado = $this->user(10, 'Convidado Share');
        $this->actingAs($dono);
        $quadro = Quadro::query()->create(['titulo' => 'Share', 'empresa_id' => 10, 'user_id' => $dono->id]);

        $this->postJson("/g/weekly-report/10/quadros/{$quadro->id}/membros", [
            'user_id' => $convidado->id,
        ])->assertCreated();

        $this->assertDatabaseHas('quadros_membros', [
            'quadro_id' => $quadro->id,
            'user_id' => $convidado->id,
            'papel' => QuadroMembro::PAPEL_MEMBRO,
        ]);

        $this->actingAs($convidado);
        $this->getJson('/g/weekly-report/10')->assertOk();
        $titulos = collect($this->getJson('/g/weekly-report/10')->json('lista'))->pluck('titulo');
        $this->assertTrue($titulos->contains('Share'));

        $this->postJson("/g/weekly-report/10/quadros/{$quadro->id}/membros", [
            'user_id' => $this->user(10, 'Outro')->id,
        ])->assertStatus(403);

        $this->actingAs($dono);
        $this->deleteJson("/g/weekly-report/10/quadros/{$quadro->id}/membros/{$convidado->id}")
            ->assertOk();
        $this->assertDatabaseMissing('quadros_membros', [
            'quadro_id' => $quadro->id,
            'user_id' => $convidado->id,
        ]);

        $this->deleteJson("/g/weekly-report/10/quadros/{$quadro->id}/membros/{$dono->id}")
            ->assertStatus(400);
    }

    public function test_store_quadro_cria_pivot_dono(): void
    {
        $user = $this->user(10, 'Criador');
        $this->actingAs($user);

        $response = $this->postJson('/g/weekly-report/10/quadros', ['titulo' => 'Novo Board']);
        $response->assertCreated();
        $quadroId = $response->json('quadro.id');
        $this->assertNotNull($quadroId);
        $this->assertTrue($response->json('quadro.sou_dono'));
        $this->assertDatabaseHas('quadros_membros', [
            'quadro_id' => $quadroId,
            'user_id' => $user->id,
            'papel' => QuadroMembro::PAPEL_DONO,
        ]);
    }

    public function test_exclui_quadro_com_soft_delete_e_quem_deletou(): void
    {
        $dono = $this->user(10, 'Dono Soft');
        $this->actingAs($dono);

        $quadro = Quadro::query()->create([
            'titulo' => 'Para Excluir',
            'empresa_id' => 10,
            'user_id' => $dono->id,
        ]);

        $this->deleteJson("/g/weekly-report/10/quadros/{$quadro->id}")->assertOk();

        $this->assertSoftDeleted('quadros', ['id' => $quadro->id]);
        $this->assertDatabaseHas('quadros', [
            'id' => $quadro->id,
            'quem_deletou_id' => $dono->id,
        ]);

        // Some da listagem e libera o nome para novo quadro
        $lista = collect($this->getJson('/g/weekly-report/10')->json('lista'))->pluck('titulo');
        $this->assertFalse($lista->contains('Para Excluir'));

        $this->postJson('/g/weekly-report/10/quadros', ['titulo' => 'Para Excluir'])
            ->assertCreated();
    }

    public function test_nao_cria_quadro_com_titulo_duplicado_do_usuario_ou_vinculo(): void
    {
        $dono = $this->user(10, 'Criador Dup');
        $colega = $this->user(10, 'Colega Dup');
        $this->actingAs($dono);

        $this->postJson('/g/weekly-report/10/quadros', ['titulo' => 'Recrutamento'])
            ->assertCreated();

        // Mesmo usuário não cria de novo (dono)
        $this->postJson('/g/weekly-report/10/quadros', ['titulo' => '  recrutamento  '])
            ->assertStatus(400)
            ->assertJsonFragment(['msg' => 'Você já possui ou está vinculado a um quadro com este nome.']);

        // Colega sem vínculo pode usar o mesmo nome
        $this->actingAs($colega);
        $this->postJson('/g/weekly-report/10/quadros', ['titulo' => 'Recrutamento'])
            ->assertCreated();

        // Colega vinculado a um quadro "Onboarding" não cria outro com o mesmo nome
        $this->actingAs($dono);
        $quadroOnboarding = Quadro::query()->create([
            'titulo' => 'Onboarding',
            'empresa_id' => 10,
            'user_id' => $dono->id,
        ]);
        $this->addMembro($quadroOnboarding, $colega);

        $this->actingAs($colega);
        $this->postJson('/g/weekly-report/10/quadros', ['titulo' => 'Onboarding'])
            ->assertStatus(400)
            ->assertJsonFragment(['msg' => 'Você já possui ou está vinculado a um quadro com este nome.']);
    }

    public function test_buscar_membros_tarefa_so_retorna_membros_do_quadro(): void
    {
        $dono = $this->user(10, 'Dono Busca');
        $noQuadro = $this->user(10, 'Alpha NoQuadro');
        $fora = $this->user(10, 'Alpha Fora');
        $this->actingAs($dono);

        $quadro = Quadro::query()->create(['titulo' => 'Busca', 'empresa_id' => 10, 'user_id' => $dono->id]);
        $this->addMembro($quadro, $noQuadro);
        $lista = ListaTarefa::query()->create([
            'titulo' => 'L1',
            'quadro_id' => $quadro->id,
            'user_id' => $dono->id,
            'ordem' => 1,
        ]);
        $tarefa = Tarefa::query()->create([
            'titulo' => 'Card',
            'lista_id' => $lista->id,
            'user_id' => $dono->id,
            'ordem' => 1,
        ]);

        $response = $this->getJson(
            "/g/weekly-report/10/quadros/{$quadro->id}/listas/{$lista->id}/tarefas/{$tarefa->id}/buscarMembros?busca=Alpha"
        );
        $response->assertOk();
        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($noQuadro->id));
        $this->assertFalse($ids->contains($fora->id));
    }

    public function test_update_membro_tarefa_rejeita_usuario_fora_do_quadro(): void
    {
        $dono = $this->user(10, 'Dono Tarefa');
        $fora = $this->user(10, 'Fora Tarefa');
        $this->actingAs($dono);
        $quadro = Quadro::query()->create(['titulo' => 'T', 'empresa_id' => 10, 'user_id' => $dono->id]);
        $lista = ListaTarefa::query()->create([
            'titulo' => 'L1',
            'quadro_id' => $quadro->id,
            'user_id' => $dono->id,
            'ordem' => 1,
        ]);
        $tarefa = Tarefa::query()->create([
            'titulo' => 'Card',
            'lista_id' => $lista->id,
            'user_id' => $dono->id,
            'ordem' => 1,
        ]);

        $this->putJson(
            "/g/weekly-report/10/quadros/{$quadro->id}/listas/{$lista->id}/tarefas/{$tarefa->id}/updateMembro",
            ['acao' => 'add', 'user_id' => $fora->id]
        )->assertStatus(400);
    }

    public function test_exclui_tarefa_com_soft_delete_e_quem_deletou(): void
    {
        Event::fake([\App\Events\WeeklyReport\TarefaEvent::class]);

        $dono = $this->user(10, 'Dono Card Soft');
        $this->actingAs($dono);

        $quadro = Quadro::query()->create([
            'titulo' => 'Board Card Soft',
            'empresa_id' => 10,
            'user_id' => $dono->id,
        ]);
        $lista = ListaTarefa::query()->create([
            'titulo' => 'Lista Soft',
            'quadro_id' => $quadro->id,
            'user_id' => $dono->id,
            'ordem' => 1,
        ]);
        $tarefa = Tarefa::query()->create([
            'titulo' => 'Card Soft',
            'lista_id' => $lista->id,
            'user_id' => $dono->id,
            'ordem' => 1,
        ]);

        $this->deleteJson(
            "/g/weekly-report/10/quadros/{$quadro->id}/listas/{$lista->id}/tarefas/{$tarefa->id}"
        )->assertOk();

        $this->assertSoftDeleted('tarefas', ['id' => $tarefa->id]);
        $this->assertDatabaseHas('tarefas', [
            'id' => $tarefa->id,
            'quem_deletou_id' => $dono->id,
        ]);
        $this->assertNull(Tarefa::query()->find($tarefa->id));
        $this->assertFalse(
            $lista->Tarefas()->whereKey($tarefa->id)->exists()
        );
    }
}
