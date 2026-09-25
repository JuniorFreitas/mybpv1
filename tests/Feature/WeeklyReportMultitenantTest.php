<?php

namespace Tests\Feature;

use App\Models\ListaTarefa;
use App\Models\Quadro;
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
        Schema::dropIfExists('quadros');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('login')->nullable();
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
            $table->text('titulo');
            $table->timestamps();
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
            $table->text('titulo');
            $table->text('descricao')->nullable();
            $table->integer('ordem')->default(1);
            $table->dateTime('datahora_inicio')->nullable();
            $table->dateTime('datahora_entrega')->nullable();
            $table->dateTime('lembrete')->nullable();
            $table->boolean('concluido')->default(false);
            $table->timestamps();
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

        Event::fake();
    }

    private function user(int $empresaId = 10): User
    {
        return User::query()->create([
            'nome' => 'User ' . $empresaId,
            'login' => "user{$empresaId}@test.com",
            'empresa_id' => $empresaId,
            'ativo' => true,
        ]);
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
        $membro = User::query()->create([
            'nome' => 'Membro Item',
            'login' => 'membro-item@test.com',
            'empresa_id' => 10,
            'ativo' => true,
        ]);
        $this->actingAs($user);

        $quadro = Quadro::query()->create(['titulo' => 'Board', 'empresa_id' => 10, 'user_id' => $user->id]);
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

        $outroTenant = User::query()->create([
            'nome' => 'Outro',
            'login' => 'outro@test.com',
            'empresa_id' => 99,
            'ativo' => true,
        ]);

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
        Queue::fake();

        $user = $this->user(10);
        $mencionado = User::query()->create([
            'nome' => 'Ana Mencionada',
            'login' => 'ana@test.com',
            'empresa_id' => 10,
            'ativo' => true,
        ]);
        $this->actingAs($user);

        $quadro = Quadro::query()->create(['titulo' => 'Board', 'empresa_id' => 10, 'user_id' => $user->id]);
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
}
