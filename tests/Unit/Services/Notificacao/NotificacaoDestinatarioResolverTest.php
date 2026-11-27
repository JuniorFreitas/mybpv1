<?php

namespace Tests\Unit\Services\Notificacao;

use App\Models\NotificacaoTipo;
use App\Services\Notificacao\NotificacaoDestinatarioResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NotificacaoDestinatarioResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->criarSchema();
    }

    public function test_sem_grupo_retorna_vazio(): void
    {
        $emails = app(NotificacaoDestinatarioResolver::class)->emails(10, NotificacaoTipo::APROVACAO_RH);

        $this->assertSame([], $emails);
    }

    public function test_dois_grupos_dedupe_mesmo_email(): void
    {
        $this->seedTipo();
        $userId = $this->criarUserRh(10, 'rh@example.com');

        $g1 = $this->criarGrupo(10, 'G1', NotificacaoTipo::ESCOPO_TODOS);
        $g2 = $this->criarGrupo(10, 'G2', NotificacaoTipo::ESCOPO_TODOS);
        DB::table('notificacao_grupo_destinatarios')->insert([
            ['grupo_id' => $g1, 'user_id' => $userId],
            ['grupo_id' => $g2, 'user_id' => $userId],
        ]);

        $emails = app(NotificacaoDestinatarioResolver::class)->emails(10, NotificacaoTipo::APROVACAO_RH);

        $this->assertSame(['rh@example.com'], $emails);
    }

    public function test_escopo_filiais_sem_contexto_nao_entra(): void
    {
        $this->seedTipo();
        $userId = $this->criarUserRh(10, 'filial@example.com');
        $g = $this->criarGrupo(10, 'Filiais', NotificacaoTipo::ESCOPO_FILIAIS);
        DB::table('notificacao_grupo_destinatarios')->insert(['grupo_id' => $g, 'user_id' => $userId]);

        $emails = app(NotificacaoDestinatarioResolver::class)->emails(10, NotificacaoTipo::APROVACAO_RH);

        $this->assertSame([], $emails);
    }

    public function test_user_inativo_nao_entra(): void
    {
        $this->seedTipo();
        $userId = $this->criarUserRh(10, 'inativo@example.com', false);
        $g = $this->criarGrupo(10, 'Todos', NotificacaoTipo::ESCOPO_TODOS);
        DB::table('notificacao_grupo_destinatarios')->insert(['grupo_id' => $g, 'user_id' => $userId]);

        $emails = app(NotificacaoDestinatarioResolver::class)->emails(10, NotificacaoTipo::APROVACAO_RH);

        $this->assertSame([], $emails);
    }

    public function test_escopo_filiais_com_cc_nao_matriz_entra(): void
    {
        $this->seedTipo();
        $userId = $this->criarUserRh(10, 'filial-ok@example.com');
        $g = $this->criarGrupo(10, 'Filiais', NotificacaoTipo::ESCOPO_FILIAIS);
        DB::table('notificacao_grupo_destinatarios')->insert(['grupo_id' => $g, 'user_id' => $userId]);

        $ccId = (int) DB::table('centro_custos')->insertGetId([
            'empresa_id' => 10,
            'label' => 'CC Filial',
            'ativo' => 1,
        ]);
        DB::table('centro_custo_filials')->insert([
            'empresa_id' => 10,
            'centro_custo_id' => $ccId,
            'cliente_filial_id' => 1,
            'deleted_at' => null,
        ]);

        $emails = app(NotificacaoDestinatarioResolver::class)->emails(10, NotificacaoTipo::APROVACAO_RH, [
            'centro_custo_id' => $ccId,
        ]);

        $this->assertSame(['filial-ok@example.com'], $emails);
    }

    public function test_especificos_so_com_cc_listado(): void
    {
        $this->seedTipo();
        $userId = $this->criarUserRh(10, 'esp@example.com');
        $g = $this->criarGrupo(10, 'Especificos', NotificacaoTipo::ESCOPO_ESPECIFICOS);
        DB::table('notificacao_grupo_destinatarios')->insert(['grupo_id' => $g, 'user_id' => $userId]);

        $ccOk = (int) DB::table('centro_custos')->insertGetId([
            'empresa_id' => 10,
            'label' => 'CC OK',
            'ativo' => 1,
        ]);
        $ccOutro = (int) DB::table('centro_custos')->insertGetId([
            'empresa_id' => 10,
            'label' => 'CC Outro',
            'ativo' => 1,
        ]);
        DB::table('notificacao_grupo_centros_custo')->insert([
            'grupo_id' => $g,
            'centro_custo_id' => $ccOk,
        ]);

        $resolver = app(NotificacaoDestinatarioResolver::class);

        $this->assertSame(['esp@example.com'], $resolver->emails(10, NotificacaoTipo::APROVACAO_RH, [
            'centro_custo_id' => $ccOk,
        ]));
        $this->assertSame([], $resolver->emails(10, NotificacaoTipo::APROVACAO_RH, [
            'centro_custo_id' => $ccOutro,
        ]));
    }

    public function test_grupo_todos_entra_com_contexto_cc(): void
    {
        $this->seedTipo();
        $userId = $this->criarUserRh(10, 'todos@example.com');
        $g = $this->criarGrupo(10, 'Todos', NotificacaoTipo::ESCOPO_TODOS);
        DB::table('notificacao_grupo_destinatarios')->insert(['grupo_id' => $g, 'user_id' => $userId]);

        $ccId = (int) DB::table('centro_custos')->insertGetId([
            'empresa_id' => 10,
            'label' => 'Qualquer',
            'ativo' => 1,
        ]);

        $emails = app(NotificacaoDestinatarioResolver::class)->emails(10, NotificacaoTipo::APROVACAO_RH, [
            'centro_custo_id' => $ccId,
        ]);

        $this->assertSame(['todos@example.com'], $emails);
    }

    public function test_destinatario_sem_privilegio_rh_nao_entra(): void
    {
        $this->seedTipo();
        $rhId = $this->criarUserRh(10, 'com-rh@example.com');
        $comumId = $this->criarUserComum(10, 'sem-rh@example.com');
        $g = $this->criarGrupo(10, 'Misto', NotificacaoTipo::ESCOPO_TODOS);
        DB::table('notificacao_grupo_destinatarios')->insert([
            ['grupo_id' => $g, 'user_id' => $rhId],
            ['grupo_id' => $g, 'user_id' => $comumId],
        ]);

        $emails = app(NotificacaoDestinatarioResolver::class)->emails(10, NotificacaoTipo::APROVACAO_RH);

        $this->assertSame(['com-rh@example.com'], $emails);
    }

    private function criarSchema(): void
    {
        Schema::dropIfExists('notificacao_grupo_centros_custo');
        Schema::dropIfExists('notificacao_grupo_destinatarios');
        Schema::dropIfExists('notificacao_grupos');
        Schema::dropIfExists('notificacao_tipos');
        Schema::dropIfExists('centro_custo_filials');
        Schema::dropIfExists('centro_custos');
        Schema::dropIfExists('papeis_habilidades');
        Schema::dropIfExists('habilidades');
        Schema::dropIfExists('papeis');
        Schema::dropIfExists('users');

        Schema::create('notificacao_tipos', function (Blueprint $table) {
            $table->string('codigo', 64)->primary();
            $table->string('nome', 120);
            $table->string('canal', 32)->default('email');
            $table->string('regra_elegibilidade', 64)->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('editavel')->default(false);
            $table->unsignedSmallInteger('ordem')->default(100);
        });

        Schema::create('notificacao_grupos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->string('tipo_codigo', 64);
            $table->string('nome', 160);
            $table->boolean('ativo')->default(true);
            $table->string('escopo_cc', 32)->default('todos');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('notificacao_grupo_destinatarios', function (Blueprint $table) {
            $table->unsignedInteger('grupo_id');
            $table->unsignedInteger('user_id');
            $table->primary(['grupo_id', 'user_id']);
        });

        Schema::create('notificacao_grupo_centros_custo', function (Blueprint $table) {
            $table->unsignedInteger('grupo_id');
            $table->unsignedInteger('centro_custo_id');
            $table->primary(['grupo_id', 'centro_custo_id']);
        });

        Schema::create('centro_custos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id')->nullable();
            $table->string('label')->nullable();
            $table->boolean('ativo')->default(true);
        });

        Schema::create('centro_custo_filials', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('centro_custo_id');
            $table->unsignedInteger('cliente_filial_id')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id')->nullable();
            $table->unsignedInteger('grupo_id')->nullable();
            $table->string('nome')->nullable();
            $table->string('login')->nullable();
            $table->boolean('ativo')->default(true);
            $table->string('tipo')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('papeis', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome')->nullable();
            $table->unsignedInteger('empresa_id')->nullable();
            $table->boolean('ativo')->default(true);
        });

        Schema::create('habilidades', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->string('descricao')->nullable();
        });

        Schema::create('papeis_habilidades', function (Blueprint $table) {
            $table->unsignedInteger('papel_id');
            $table->unsignedInteger('habilidade_id');
        });
    }

    private function seedTipo(): void
    {
        DB::table('notificacao_tipos')->insert([
            'codigo' => NotificacaoTipo::APROVACAO_RH,
            'nome' => 'Aprovação RH',
            'canal' => 'email',
            'regra_elegibilidade' => NotificacaoTipo::REGRA_PRIVILEGIOS_RH,
            'ativo' => 1,
            'editavel' => 1,
            'ordem' => 10,
        ]);
    }

    private function criarGrupo(int $empresaId, string $nome, string $escopo): int
    {
        return (int) DB::table('notificacao_grupos')->insertGetId([
            'empresa_id' => $empresaId,
            'tipo_codigo' => NotificacaoTipo::APROVACAO_RH,
            'nome' => $nome,
            'ativo' => 1,
            'escopo_cc' => $escopo,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function garantirPapelRh(): int
    {
        $papelId = 1;
        if (!DB::table('papeis')->where('id', $papelId)->exists()) {
            DB::table('papeis')->insert(['id' => $papelId, 'nome' => 'RH', 'empresa_id' => 10, 'ativo' => 1]);
        }
        $habId = DB::table('habilidades')->where('nome', 'privilegio_gestao_rh')->value('id');
        if (!$habId) {
            $habId = DB::table('habilidades')->insertGetId([
                'nome' => 'privilegio_gestao_rh',
                'descricao' => 'RH',
            ]);
        }
        $linked = DB::table('papeis_habilidades')
            ->where('papel_id', $papelId)
            ->where('habilidade_id', $habId)
            ->exists();
        if (!$linked) {
            DB::table('papeis_habilidades')->insert([
                'papel_id' => $papelId,
                'habilidade_id' => $habId,
            ]);
        }

        return $papelId;
    }

    private function criarUserRh(int $empresaId, string $login, bool $ativo = true): int
    {
        $papelId = $this->garantirPapelRh();

        return (int) DB::table('users')->insertGetId([
            'empresa_id' => $empresaId,
            'grupo_id' => $papelId,
            'nome' => 'RH',
            'login' => $login,
            'ativo' => $ativo ? 1 : 0,
            'tipo' => 'User',
            'deleted_at' => null,
        ]);
    }

    private function criarUserComum(int $empresaId, string $login): int
    {
        if (!DB::table('papeis')->where('id', 99)->exists()) {
            DB::table('papeis')->insert(['id' => 99, 'nome' => 'Comum', 'empresa_id' => $empresaId, 'ativo' => 1]);
        }

        return (int) DB::table('users')->insertGetId([
            'empresa_id' => $empresaId,
            'grupo_id' => 99,
            'nome' => 'Comum',
            'login' => $login,
            'ativo' => 1,
            'tipo' => 'User',
            'deleted_at' => null,
        ]);
    }
}
