<?php

namespace Tests\Feature;

use App\Http\Controllers\AniversariantesController;
use App\Models\Admissao;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AniversariantesListaAdmitidosTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);
        Gate::define('administracao_aniversariantes', fn () => true);

        Schema::dropIfExists('demissaos');
        Schema::dropIfExists('admissoes');
        Schema::dropIfExists('feedback_curriculos');
        Schema::dropIfExists('curriculo_telefone');
        Schema::dropIfExists('parabens_enviados');
        Schema::dropIfExists('curriculos');
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

        Schema::create('curriculo_telefone', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->string('pais')->nullable();
            $table->string('numero')->nullable();
            $table->unsignedBigInteger('curriculo_id');
            $table->boolean('principal')->default(false);
        });

        Schema::create('curriculos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('email')->nullable();
            $table->string('rg')->nullable();
            $table->string('orgao_expeditor')->nullable();
            $table->date('nascimento')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('feedback_curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id');
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('admissoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('demissaos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->timestamps();
        });

        Schema::create('parabens_enviados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->integer('ano');
            $table->string('status')->nullable();
        });

        $pdo = DB::connection()->getPdo();
        $pdo->sqliteCreateFunction('month', fn ($data) => (int) date('m', strtotime((string) $data)), 1);
        $pdo->sqliteCreateFunction('day', fn ($data) => (int) date('d', strtotime((string) $data)), 1);
    }

    public function test_lista_do_mes_mostra_somente_status_admitido_sem_demissao(): void
    {
        $user = User::query()->create([
            'nome' => 'RH Teste',
            'login' => 'rh@teste.com',
            'empresa_id' => 50,
            'ativo' => true,
        ]);

        $nesteMes = now()->copy()->subYears(30)->toDateString();
        $outroMes = now()->copy()->subMonth()->subYears(28)->toDateString();

        $this->inserirPessoa(10, 50, 'Admitido', $nesteMes, Admissao::STATUS_ADMISSAO_ADMITIDO);
        $this->inserirPessoa(11, 50, 'Em processo', $nesteMes, Admissao::STATUS_ADMISSAO_PENDENTEASO);
        $this->inserirPessoa(12, 50, 'Demitido', $nesteMes, Admissao::STATUS_ADMISSAO_ADMITIDO, true);
        $this->inserirPessoa(13, 50, 'Outro mes', $outroMes, Admissao::STATUS_ADMISSAO_ADMITIDO);
        $this->inserirPessoa(14, 99, 'Outra empresa', $nesteMes, Admissao::STATUS_ADMISSAO_ADMITIDO);
        $this->inserirPessoa(15, 50, 'Sem WhatsApp', $nesteMes, Admissao::STATUS_ADMISSAO_ADMITIDO);

        $this->inserirTelefone(10, TelefoneCurriculo::TIPO_CELULAR, '(98) 98888-1010', false);
        $this->inserirTelefone(10, TelefoneCurriculo::TIPO_WHATS, '(98) 99999-1010', false);
        $this->inserirTelefone(10, TelefoneCurriculo::TIPO_WHATS, '(98) 97777-1010', true);
        $this->inserirTelefone(15, TelefoneCurriculo::TIPO_CELULAR, '(98) 96666-1515', true);

        $this->actingAs($user);

        $response = app(AniversariantesController::class)->atualizar(new Request());
        $porId = collect($response->getData(true)['dados'])->keyBy('id');

        $this->assertEqualsCanonicalizing([10, 15], $porId->keys()->all());
        $this->assertSame('(98) 97777-1010', $porId[10]['whatsapp']);
        $this->assertSame('Não informado', $porId[15]['whatsapp']);
    }

    private function inserirTelefone(int $curriculoId, string $tipo, string $numero, bool $principal): void
    {
        DB::table('curriculo_telefone')->insert([
            'tipo' => $tipo,
            'pais' => '55',
            'numero' => $numero,
            'curriculo_id' => $curriculoId,
            'principal' => $principal,
        ]);
    }

    private function inserirPessoa(int $id, int $empresaId, string $nome, string $nascimento, ?string $status, bool $demitido = false): void
    {
        DB::table('users')->insert([
            'id' => $id,
            'nome' => $nome,
            'login' => "p{$id}@teste.com",
            'empresa_id' => $empresaId,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('curriculos')->insert([
            'id' => $id,
            'nome' => $nome,
            'email' => "p{$id}@teste.com",
            'nascimento' => $nascimento,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('feedback_curriculos')->insert([
            'id' => $id,
            'curriculo_id' => $id,
            'empresa_id' => $empresaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($status !== null) {
            DB::table('admissoes')->insert([
                'feedback_id' => $id,
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($demitido) {
            DB::table('demissaos')->insert([
                'feedback_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
