<?php

namespace Tests\Unit\Services\Admissao;

use App\Models\User;
use App\Services\Admissao\AdmissaoCpfLookupService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdmissaoCpfLookupServiceTest extends TestCase
{
    private AdmissaoCpfLookupService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdmissaoCpfLookupService();

        foreach (['admissoes', 'feedback_curriculos', 'curriculos', 'users'] as $tabela) {
            Schema::dropIfExists($tabela);
        }

        // Schema mínimo apenas para permitir a execução das consultas (whereHas/exists)
        // usadas pelo AdmissaoCpfLookupService::lookup(). "users" é necessária porque o
        // escopo multi-tenant legado de Curriculo (App\Scopes\ScopeEmpresa) faz um
        // whereHas('Pessoa', ...) contra a tabela de usuários.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('curriculos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('cpf')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('feedback_curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('admissoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Autentica um usuário fictício: Curriculo e FeedbackCurriculo aplicam escopo
        // multi-tenant (empresa_id) nas global scopes do model.
        $usuario = (new User())->forceFill(['id' => 1, 'empresa_id' => 10, 'nome' => 'RH']);
        Auth::setUser($usuario);
    }

    public function test_cpf_sem_curriculo_cadastrado_retorna_achou_false(): void
    {
        $resultado = $this->service->lookup('123.456.789-09');

        $this->assertSame(200, $resultado['status']);
        $this->assertSame(['achou' => false, 'ex_funcionario' => false], $resultado['body']);
    }
}
