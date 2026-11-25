<?php

namespace Tests\Unit\Services\Admissao;

use App\Models\User;
use App\Services\Admissao\AdmissaoProcessoFiltroQuery;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Cobre AdmissaoProcessoFiltroQuery::aplicar (extraído de AdmissaoController::filtro,
 * TD-002 fatia 3).
 *
 * A query sempre passa pelo scope FeedbackCurriculo::filtrarPorCnpjECentroCusto, que
 * resolve matriz/filial do usuário logado via Cliente/ClienteFilial/CentroCusto. Por
 * isso o schema mínimo precisa cobrir essa cadeia (não apenas as tabelas de Admissão)
 * para `aplicar()` não quebrar antes de chegarmos nos filtros Admitidos/Demitidos.
 */
class AdmissaoProcessoFiltroQueryTest extends TestCase
{
    private AdmissaoProcessoFiltroQuery $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        $this->service = new AdmissaoProcessoFiltroQuery();

        foreach (['centro_custos', 'cliente_filials', 'empresa_clientes', 'clientes', 'users'] as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // "Cliente" (lotação/matriz) resolvido a partir do próprio empresa_id do
        // usuário logado (Cliente::findFiliarOuMatriz -> Cliente::Cnpjs).
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('cnpj')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('razao_social')->nullable();
            $table->boolean('ativo')->default(true);
            $table->string('logradouro')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('municipio')->nullable();
            $table->string('uf')->nullable();
            $table->timestamps();
        });

        // Pivot usado pelo global scope "scopeCliente" do model Cliente
        // (auth()->user()->ClientesEmpresa->pluck('id')).
        Schema::create('empresa_clientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->timestamps();
        });

        Schema::create('cliente_filials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->json('dados')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // CentroCusto usa TenantTrait (ScopeEmpresa) -> precisa de empresa_id.
        Schema::create('centro_custos', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
        });

        // Usuário autenticado (empresa_id = 10).
        $usuario = (new User())->forceFill(['id' => 1, 'empresa_id' => 10, 'nome' => 'RH']);
        Auth::setUser($usuario);

        // "Cliente" da empresa 10 (sem filial) + pivot que o scopeCliente do model
        // Cliente exige para liberar o find($empresaId).
        DB::table('clientes')->insert([
            'id' => 10,
            'cnpj' => '11.222.333/0001-81',
            'nome_fantasia' => 'Empresa Teste',
            'razao_social' => 'Empresa Teste LTDA',
            'ativo' => true,
        ]);
        DB::table('users')->insert([
            'id' => 10,
            'empresa_id' => 10,
            'nome' => 'Empresa Teste (cliente)',
        ]);
        DB::table('empresa_clientes')->insert([
            'empresa_id' => 10,
            'cliente_id' => 10,
        ]);
    }

    public function test_sem_campo_demitido_aplica_scope_admitidos(): void
    {
        $sql = strtolower($this->service->aplicar(new Request())->toSql());

        // FeedbackCurriculo::scopeAdmitidos() -> whereDoesntHave('Demissao').
        $this->assertStringContainsString('not exists', $sql);
    }

    public function test_com_campo_demitido_aplica_scope_demitidos(): void
    {
        $sql = strtolower($this->service->aplicar(new Request(['campoDemitido' => '1']))->toSql());

        // FeedbackCurriculo::scopeDemitidos() -> has('Demissao') (exists, sem "not").
        $this->assertStringContainsString('exists', $sql);
        $this->assertStringNotContainsString('not exists', $sql);
    }

    public function test_filtro_campo_cliente_aplica_where_cliente_id(): void
    {
        $sql = strtolower($this->service->aplicar(new Request(['campoCliente' => 5]))->toSql());

        $this->assertStringContainsString('cliente_id', $sql);
    }

    public function test_filtro_campo_cpf_aplica_where_has_curriculo(): void
    {
        $sql = strtolower($this->service->aplicar(new Request(['campoCPF' => '123.456.789-09']))->toSql());

        $this->assertStringContainsString('exists', $sql);
    }
}
