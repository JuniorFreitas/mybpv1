<?php

namespace Tests\Unit\Services\Admissao;

use App\Models\Admissao;
use App\Models\User;
use App\Services\Admissao\AdmissaoStoreService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdmissaoStoreServiceTest extends TestCase
{
    private AdmissaoStoreService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdmissaoStoreService();

        foreach (['vagas_abertas', 'vagas', 'users'] as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vagas', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
        });

        Schema::create('vagas_abertas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vaga_id')->nullable();
            $table->unsignedBigInteger('municipio_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
        });

        $authUser = new User();
        $authUser->forceFill(['id' => 1, 'empresa_id' => 10, 'nome' => 'RH']);
        Auth::setUser($authUser);
    }

    #[Test]
    public function exige_telefone_quando_lista_vazia(): void
    {
        $vagaId = DB::table('vagas')->insertGetId([
            'nome' => 'Analista',
            'empresa_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $vagaAbertaId = DB::table('vagas_abertas')->insertGetId([
            'vaga_id' => $vagaId,
            'municipio_id' => 1,
            'empresa_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = Request::create('/admissao', 'POST', [
            'feedback' => [
                'vaga_id' => $vagaAbertaId,
                'autocomplete_label_vaga_modal' => 'Vaga - SP',
                'interesse' => 'true',
            ],
            'curriculo' => [
                'email' => 'teste@example.com',
                'pcd' => 'false',
                'cpf' => '12345678909',
                'telefones' => [],
            ],
            'parecer_rh' => [
                'ex_funcionario' => 'false',
                'indicacao' => 'false',
                'turnos_seis_por_dois' => 'false',
            ],
            'parecer_rota' => [],
            'parecer_tecnica' => [],
            'parecer_teste' => [],
            'admissao' => [
                'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
                'data_admissao' => '01/01/2026',
                'padrao_treinamento_id' => null,
                'dados_admissoes' => [
                    'ctps_numero' => null,
                    'ctps_serie' => null,
                    'ctps_data_emissao' => null,
                    'titulo_eleitor_numero' => null,
                    'titulo_eleitor_sessao' => null,
                    'titulo_eleitor_zona' => null,
                ],
            ],
            'resultado_integrado' => [
                'documentos_entregue' => 'false',
                'documentos_entregue_data' => null,
                'encaminhado_exame' => 'false',
                'encaminhado_exame_data' => null,
                'encaminhado_treinamento' => 'false',
                'encaminhado_treinamento_data' => null,
            ],
        ]);

        $result = $this->service->criar($request);

        $this->assertSame(400, $result['status']);
        $this->assertSame('Por favor insira um telefone', $result['body']['msg']);
    }
}
