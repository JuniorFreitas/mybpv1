<?php

namespace Tests\Unit;

use App\Models\CartaOferta;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\User;
use App\Models\VagasAbertas;
use App\Services\CartaOferta\CartaOfertaContextoBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CartaOfertaContextoBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);

        Schema::dropIfExists('curriculo_carta_oferta');
        Schema::dropIfExists('vaga_projetos');
        Schema::dropIfExists('projetos');
        Schema::dropIfExists('vagas_abertas');
        Schema::dropIfExists('vagas');
        Schema::dropIfExists('curriculos');
        Schema::dropIfExists('users');
        Schema::dropIfExists('clientes');

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('razao_social')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->string('cpf')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vagas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vagas_abertas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('vaga_id')->nullable();
            $table->string('titulo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('projetos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->timestamps();
        });

        Schema::create('vaga_projetos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('projeto_id')->nullable();
            $table->unsignedBigInteger('vaga_aberta_id')->nullable();
            $table->integer('qnt_total')->default(0);
            $table->integer('qnt_preenchida')->default(0);
        });

        Schema::create('curriculo_carta_oferta', function (Blueprint $table) {
            $table->id();
            $table->string('token')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('vagas_abertas_id')->nullable();
            $table->unsignedBigInteger('vaga_projeto_id')->nullable();
            $table->string('status')->nullable();
            $table->string('local')->nullable();
            $table->string('setor')->nullable();
            $table->string('salario')->nullable();
            $table->date('data_inicio')->nullable();
            $table->json('logs')->nullable();
            $table->timestamps();
        });

        $this->actingAs($this->usuario(10));
    }

    public function test_fake_preenche_placeholders_padrao(): void
    {
        $empresa = Cliente::query()->create([
            'razao_social' => 'ACME LTDA',
            'nome_fantasia' => 'ACME',
            'cnpj' => '11.111.111/0001-11',
        ]);

        $dados = (new CartaOfertaContextoBuilder())->fake($empresa);

        $this->assertSame('João da Silva', $dados['colaborador']['nome']);
        $this->assertSame('ACME', $dados['empresa']['nome_fantasia']);
        $this->assertSame('Recursos Humanos', $dados['setor']);
    }

    public function test_para_carta_usa_setor_salario_e_fallback(): void
    {
        $empresaId = DB::table('clientes')->insertGetId([
            'razao_social' => 'Empresa X',
            'nome_fantasia' => 'Emp X',
            'cnpj' => '22.222.222/0001-22',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $curriculoId = 501;
        DB::table('users')->insert([
            'id' => $curriculoId,
            'nome' => 'Ana Preview',
            'empresa_id' => $empresaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('curriculos')->insert([
            'id' => $curriculoId,
            'empresa_id' => $empresaId,
            'nome' => 'Ana Preview',
            'cpf' => '123.456.789-00',
            'email' => 'ana@exemplo.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $vagaId = DB::table('vagas')->insertGetId([
            'empresa_id' => $empresaId,
            'nome' => 'Assistente Administrativo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $vagaAberta = VagasAbertas::withoutGlobalScopes()->create([
            'empresa_id' => $empresaId,
            'vaga_id' => $vagaId,
            'titulo' => 'Titulo vaga',
        ]);

        $carta = CartaOferta::withoutGlobalScopes()->create([
            'token' => 'tok-teste',
            'empresa_id' => $empresaId,
            'curriculo_id' => $curriculoId,
            'vagas_abertas_id' => $vagaAberta->id,
            'status' => CartaOferta::STATUS_PENDENTE_ANEXO,
            'local' => CartaOferta::LOCAL_MYBP,
            'setor' => 'Operações',
            'salario' => 'R$ 2.000,00',
            'data_inicio' => '2026-04-01',
            'logs' => [],
        ]);

        $this->actingAs($this->usuario($empresaId));

        $dados = (new CartaOfertaContextoBuilder())->paraCarta($carta->fresh());

        $this->assertSame('ANA PREVIEW', mb_strtoupper($dados['colaborador']['nome']));
        $this->assertSame('Assistente Administrativo', $dados['cargo']);
        $this->assertSame('Operações', $dados['setor']);
        $this->assertSame('R$ 2.000,00', $dados['salario']);
        $this->assertNotSame(CartaOfertaContextoBuilder::NAO_INFORMADO, $dados['data_inicio']);
    }

    public function test_para_preview_fallback_nao_informado(): void
    {
        $curriculo = new Curriculo([
            'nome' => 'Sem Cargo',
            'email' => 'a@b.com',
        ]);

        $dados = (new CartaOfertaContextoBuilder())->paraPreview($curriculo, null, null, []);

        $this->assertSame(CartaOfertaContextoBuilder::NAO_INFORMADO, $dados['cargo']);
        $this->assertSame(CartaOfertaContextoBuilder::NAO_INFORMADO, $dados['setor']);
        $this->assertSame(CartaOfertaContextoBuilder::NAO_INFORMADO, $dados['empresa']['razao_social']);
    }

    public function test_salario_zero_ou_vazio_nao_aparece_no_contexto(): void
    {
        $builder = new CartaOfertaContextoBuilder();

        $this->assertNull($builder->normalizarSalarioParaPersistencia('0,00'));
        $this->assertNull($builder->normalizarSalarioParaPersistencia('R$ 0,00'));
        $this->assertNull($builder->normalizarSalarioParaPersistencia(''));
        $this->assertSame('1.500,00', $builder->normalizarSalarioParaPersistencia('1.500,00'));

        $this->assertSame('', $builder->formatarSalario('0,00'));
        $this->assertSame('', $builder->formatarSalario(null));
        $this->assertSame('R$ 1.500,00', $builder->formatarSalario('1.500,00'));
        $this->assertSame('R$ 2.000,00', $builder->formatarSalario('R$ 2.000,00'));

        $curriculo = new Curriculo(['nome' => 'X', 'email' => 'x@y.com']);
        $dados = $builder->paraPreview($curriculo, null, null, ['salario' => '0,00']);

        $this->assertSame('', $dados['salario']);
        $this->assertSame('', $dados['bloco_salario']);
    }

    private function usuario(int $empresaId): User
    {
        $user = new User([
            'nome' => 'RH Teste',
            'empresa_id' => $empresaId,
            'ativo' => true,
            'temp' => false,
        ]);
        $user->id = 1;

        return $user;
    }
}
