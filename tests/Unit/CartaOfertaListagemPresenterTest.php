<?php

namespace Tests\Unit;

use App\Models\CartaOferta;
use App\Models\Curriculo;
use App\Models\User;
use App\Services\CartaOferta\CartaOfertaListagemPresenter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CartaOfertaListagemPresenterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->garantirTabelas();
    }

    public function test_payload_enxuto_mascara_cpf_e_omite_relacoes(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'nome' => 'Maria RH',
            'empresa_id' => 10,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('curriculos')->insert([
            'id' => 20,
            'nome' => 'João Candidato',
            'cpf' => '52998224725',
            'email' => 'joao@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('curriculo_carta_oferta')->insert([
            'id' => 30,
            'token' => 'tok-1',
            'public_id' => 'pub-1',
            'empresa_id' => 10,
            'user_id' => 1,
            'curriculo_id' => 20,
            'status' => CartaOferta::STATUS_AGUARDANDO_RH,
            'local' => CartaOferta::LOCAL_MYBP,
            'setor' => 'RH',
            'salario' => 'R$ 2.000,00',
            'logs' => json_encode([
                [
                    'data' => '2026-10-10 09:00:00',
                    'mensagem' => 'Carta oferta criada',
                    'status' => CartaOferta::STATUS_PENDENTE_ANEXO,
                    'usuario' => 'Maria RH',
                ],
                [
                    'data' => '2026-10-11 14:30:00',
                    'mensagem' => 'Carta de oferta enviada pelo candidato',
                    'status' => 'Enviado pelo candidato',
                    'usuario' => 'João Candidato',
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $curriculo = Curriculo::withoutGlobalScopes()->findOrFail(20);
        $curriculo->setRelation('TelPrincipal', null);

        $carta = CartaOferta::withoutGlobalScopes()->findOrFail(30);
        $carta->setRelation('curriculo', $curriculo);
        $carta->setRelation('user', User::withoutGlobalScopes()->findOrFail(1));
        $carta->setRelation('vagaAberta', null);
        $carta->setRelation('vagaProjeto', null);
        $carta->setRelation('anexo', null);

        $payload = app(CartaOfertaListagemPresenter::class)->apresentar(
            $carta,
            [],
            'empresa-teste',
            'https://app.test'
        );

        $this->assertSame([
            'id',
            'status',
            'local',
            'token',
            'candidato_nome',
            'cpf_mascarado',
            'cpf',
            'contato_email',
            'contato_telefone',
            'cargo_nome',
            'setor',
            'salario',
            'projeto_nome',
            'data_inicio_exibicao',
            'atualizacao_exibicao',
            'criador_nome',
            'envio_em',
            'fluxo_candidato',
            'fluxo_rh',
            'link_publico',
            'documento_para_assinatura',
            'anexo',
        ], array_keys($payload));

        $this->assertSame('JOÃO CANDIDATO', $payload['candidato_nome']);
        $this->assertSame('529.***.***-25', $payload['cpf_mascarado']);
        $this->assertNull($payload['cpf']);
        $this->assertSame('Maria RH', $payload['criador_nome']);
        $this->assertSame('https://app.test/empresa-teste/carta-oferta/pub-1', $payload['link_publico']);
        $this->assertSame('aprovado', $payload['fluxo_candidato']['status']);
        $this->assertSame('Aceito', $payload['fluxo_candidato']['texto']);
        $this->assertSame('João Candidato', $payload['fluxo_candidato']['nome']);
        $this->assertNotEmpty($payload['fluxo_candidato']['data']);
        $this->assertSame('aguardando', $payload['fluxo_rh']['status']);
        $this->assertSame('Aguardando', $payload['fluxo_rh']['texto']);
        $this->assertArrayNotHasKey('logs', $payload);
        $this->assertArrayNotHasKey('curriculo', $payload);
        $this->assertArrayNotHasKey('acesso_codigo_hash', $payload);
    }

    public function test_projeto_nome_usa_vaga_projeto_ou_fallback_da_vaga_aberta(): void
    {
        DB::table('curriculos')->insert([
            'id' => 22,
            'nome' => 'Bruno',
            'cpf' => '52998224725',
            'email' => 'bruno@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('curriculo_carta_oferta')->insert([
            'id' => 32,
            'token' => 'tok-3',
            'public_id' => 'pub-3',
            'empresa_id' => 10,
            'curriculo_id' => 22,
            'vagas_abertas_id' => 88,
            'status' => CartaOferta::STATUS_AGUARDANDO_RH,
            'local' => CartaOferta::LOCAL_MYBP,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $curriculo = Curriculo::withoutGlobalScopes()->findOrFail(22);
        $curriculo->setRelation('TelPrincipal', null);

        $projeto = new \App\Models\Projeto(['nome' => 'Obra Norte']);
        $projeto->id = 5;
        $vagaProjeto = new \App\Models\VagaProjeto(['projeto_id' => 5, 'vaga_aberta_id' => 88]);
        $vagaProjeto->id = 9;
        $vagaProjeto->setRelation('Projeto', $projeto);

        $vagaAberta = new \App\Models\VagasAbertas(['titulo' => 'Operador']);
        $vagaAberta->id = 88;
        $vagaAberta->setRelation('Projetos', collect([$vagaProjeto]));
        $vagaAberta->setRelation('Vaga', null);

        $carta = CartaOferta::withoutGlobalScopes()->findOrFail(32);
        $carta->setRelation('curriculo', $curriculo);
        $carta->setRelation('vagaAberta', $vagaAberta);
        $carta->setRelation('vagaProjeto', null);
        $carta->setRelation('anexo', null);

        $payload = app(CartaOfertaListagemPresenter::class)->apresentar(
            $carta,
            [],
            'empresa-teste',
            'https://app.test'
        );

        $this->assertSame('Obra Norte', $payload['projeto_nome']);
    }

    public function test_inclui_cpf_somente_quando_pendente_anexo(): void
    {
        DB::table('curriculos')->insert([
            'id' => 21,
            'nome' => 'Ana',
            'cpf' => '39053344705',
            'email' => 'ana@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('curriculo_carta_oferta')->insert([
            'id' => 31,
            'token' => 'tok-2',
            'public_id' => 'pub-2',
            'empresa_id' => 10,
            'curriculo_id' => 21,
            'status' => CartaOferta::STATUS_PENDENTE_ANEXO,
            'local' => CartaOferta::LOCAL_MYBP,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $curriculo = Curriculo::withoutGlobalScopes()->findOrFail(21);
        $curriculo->setRelation('TelPrincipal', null);

        $carta = CartaOferta::withoutGlobalScopes()->findOrFail(31);
        $carta->setRelation('curriculo', $curriculo);
        $carta->setRelation('vagaAberta', null);
        $carta->setRelation('vagaProjeto', null);
        $carta->setRelation('anexo', null);

        $payload = app(CartaOfertaListagemPresenter::class)->apresentar(
            $carta,
            [],
            'empresa-teste',
            'https://app.test'
        );

        $this->assertSame('39053344705', $payload['cpf']);
        $this->assertSame('390.***.***-05', $payload['cpf_mascarado']);
    }

    private function garantirTabelas(): void
    {
        foreach (['curriculo_carta_oferta', 'curriculos', 'users'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('curriculos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('cpf')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('curriculo_carta_oferta', function (Blueprint $table) {
            $table->id();
            $table->string('token')->nullable();
            $table->uuid('public_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('vagas_abertas_id')->nullable();
            $table->unsignedBigInteger('vaga_projeto_id')->nullable();
            $table->unsignedInteger('arquivo_id')->nullable();
            $table->string('status')->nullable();
            $table->string('local')->nullable();
            $table->string('setor')->nullable();
            $table->string('salario')->nullable();
            $table->date('data_inicio')->nullable();
            $table->json('logs')->nullable();
            $table->timestamps();
        });
    }
}
