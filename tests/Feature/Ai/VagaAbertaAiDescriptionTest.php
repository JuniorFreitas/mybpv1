<?php

namespace Tests\Feature\Ai;

use App\Application\UseCases\GenerateVagaAbertaDescription;
use App\Models\CategoriaVagas;
use App\Models\Cbo;
use App\Models\CboFamilia;
use App\Models\ClienteConfig;
use App\Models\Municipio;
use App\Models\User;
use App\Models\Vaga;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VagaAbertaAiDescriptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->garantirMigrations();
        $this->registrarGates();
        $this->withoutMiddleware();

        Cache::forget('ai:health:gemini:cooldown_until');

        config([
            'services.gemini.api_key' => 'chave-de-teste',
            'services.gemini.model' => 'gemini-2.5-flash-lite',
            'services.gemini.models' => 'gemini-2.5-flash-lite,gemini-2.5-flash',
            'services.gemini.allowed_hosts' => ['generativelanguage.googleapis.com'],
        ]);
    }

    public function testGeraDescricaoComSucesso(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        $this->habilitarIaParaEmpresa($user->empresa_id);

        $categoria = CategoriaVagas::query()->create(['titulo' => 'Tecnologia', 'ativo' => true]);
        $vaga = Vaga::query()->create(['nome' => 'Desenvolvedor PHP', 'ativo' => true, 'empresa_id' => $user->empresa_id, 'categoria_id' => $categoria->id]);
        $municipio = Municipio::query()->create(['nome' => 'Curitiba', 'uf' => 'PR', 'capital' => true]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => '<p>Vaga para Desenvolvedor PHP em Curitiba.</p>']]]],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/g/cadastro/vagas-abertas/ai-descricao', [
            'vaga_id' => $vaga->id,
            'municipio_id' => $municipio->id,
            'titulo' => 'Desenvolvedor PHP Pleno',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.text', '<p>Vaga para Desenvolvedor PHP em Curitiba.</p>');
        $response->assertJsonStructure(['success', 'data' => ['text', 'model', 'model_id', 'disclaimer']]);
    }

    public function testPromptIncluiResumoDaOcupacaoQuandoCboTemFamilia(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        $this->habilitarIaParaEmpresa($user->empresa_id);

        $familia = CboFamilia::query()->create([
            'codigo' => '3171',
            'titulo' => 'Técnicos de desenvolvimento',
            'descricao_sumaria' => 'Desenvolvem e mantêm sistemas de informação.',
            'ativo' => true,
        ]);
        $cbo = Cbo::query()->create([
            'codigo' => '317110',
            'titulo' => 'Programador de sistemas',
            'codigo_familia' => $familia->codigo,
            'ativo' => true,
        ]);
        $vaga = Vaga::query()->create(['nome' => 'Desenvolvedor PHP', 'ativo' => true, 'empresa_id' => $user->empresa_id, 'cbo_id' => $cbo->id]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => '<h3>Sobre a vaga</h3><p>...</p>']]]],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/g/cadastro/vagas-abertas/ai-descricao', [
            'vaga_id' => $vaga->id,
        ]);

        $response->assertOk();

        $minWords = (new \ReflectionClassConstant(GenerateVagaAbertaDescription::class, 'MIN_WORDS'))->getValue();
        $maxWords = (new \ReflectionClassConstant(GenerateVagaAbertaDescription::class, 'MAX_WORDS'))->getValue();

        Http::assertSent(function ($request) use ($minWords, $maxWords) {
            $body = $request->data();

            return str_contains($body['system_instruction']['parts'][0]['text'], (string) $minWords)
                && str_contains($body['system_instruction']['parts'][0]['text'], (string) $maxWords)
                && str_contains($body['contents'][0]['parts'][0]['text'], 'Desenvolvem e mantêm sistemas de informação.');
        });
    }

    public function testEscolhaDoModeloEAleatoriaEntreAsRequisicoes(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        $this->habilitarIaParaEmpresa($user->empresa_id);

        $vaga = Vaga::query()->create(['nome' => 'Analista Fiscal', 'ativo' => true, 'empresa_id' => $user->empresa_id]);

        config(['services.gemini.models' => 'gemini-2.5-flash-lite,gemini-2.5-flash,gemini-2.0-flash']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => '<p>...</p>']]]],
                ],
            ], 200),
        ]);

        $modelosUsados = [];
        for ($i = 0; $i < 30; $i++) {
            $response = $this->postJson('/g/cadastro/vagas-abertas/ai-descricao', ['vaga_id' => $vaga->id]);
            $response->assertOk();
            $modelosUsados[$response->json('data.model_id')] = true;
        }

        // Com 30 tentativas e 3 modelos, a chance de sempre cair no mesmo por acaso é desprezível:
        // se a rotação ainda fosse round-robin/fixa, sempre começaria pelo mesmo modelo.
        $this->assertGreaterThan(1, count($modelosUsados), 'A escolha do modelo deveria variar entre as requisições.');
    }

    public function testPrimeiroModeloFalhaESegundoConclui(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        $this->habilitarIaParaEmpresa($user->empresa_id);

        $vaga = Vaga::query()->create(['nome' => 'Analista Fiscal', 'ativo' => true, 'empresa_id' => $user->empresa_id]);

        Http::fake([
            'generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent' => Http::response([], 503),
            'generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => '<p>Vaga para Analista Fiscal.</p>']]]],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/g/cadastro/vagas-abertas/ai-descricao', [
            'vaga_id' => $vaga->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.model_id', 'gemini-2.5-flash');
    }

    public function testRateLimitInterrompeRotacaoEEntraEmCooldown(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        $this->habilitarIaParaEmpresa($user->empresa_id);

        $vaga = Vaga::query()->create(['nome' => 'Analista Fiscal', 'ativo' => true, 'empresa_id' => $user->empresa_id]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([], 429),
        ]);

        $primeira = $this->postJson('/g/cadastro/vagas-abertas/ai-descricao', ['vaga_id' => $vaga->id]);
        $primeira->assertStatus(429);
        // Só o primeiro modelo da rotação deve ter sido chamado: um 429 não pode disparar fallback
        // para o próximo modelo, já que ambos compartilham a mesma chave/cota do provedor.
        Http::assertSentCount(1);

        $segunda = $this->postJson('/g/cadastro/vagas-abertas/ai-descricao', ['vaga_id' => $vaga->id]);
        $segunda->assertStatus(429);
        // Dentro do cooldown, a segunda requisição nem chega a chamar o provedor.
        Http::assertSentCount(1);
    }

    public function testRetorna503SemChaveConfigurada(): void
    {
        config(['services.gemini.api_key' => null]);

        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        $this->habilitarIaParaEmpresa($user->empresa_id);

        $vaga = Vaga::query()->create(['nome' => 'Auxiliar Administrativo', 'ativo' => true, 'empresa_id' => $user->empresa_id]);

        $response = $this->postJson('/g/cadastro/vagas-abertas/ai-descricao', [
            'vaga_id' => $vaga->id,
        ]);

        $response->assertStatus(503);
    }

    public function testRetorna403QuandoIaNaoHabilitadaParaEmpresa(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        // Sem habilitarIaParaEmpresa(): nem existe ClienteConfig para a empresa, então o flag é falso por padrão.

        $vaga = Vaga::query()->create(['nome' => 'Analista Fiscal', 'ativo' => true, 'empresa_id' => $user->empresa_id]);

        Http::fake();

        $response = $this->postJson('/g/cadastro/vagas-abertas/ai-descricao', [
            'vaga_id' => $vaga->id,
        ]);

        $response->assertStatus(403);
        Http::assertNothingSent();
    }

    public function testRetorna404ParaVagaDeOutraEmpresa(): void
    {
        $user = $this->criarUsuarioEmpresa();

        $vagaDeOutraEmpresa = Vaga::query()->create(['nome' => 'Cargo de Outra Empresa', 'ativo' => true, 'empresa_id' => 999]);

        $this->actingAs($user);

        $response = $this->postJson('/g/cadastro/vagas-abertas/ai-descricao', [
            'vaga_id' => $vagaDeOutraEmpresa->id,
        ]);

        $response->assertStatus(404);
    }

    private function garantirMigrations(): void
    {
        if (! Schema::hasTable('vagas')) {
            $this->artisan('migrate', ['--path' => 'database/migrations/2021_07_05_220855_create_vagas_table.php']);
        }
        if (! Schema::hasTable('municipios')) {
            $this->artisan('migrate', ['--path' => 'database/migrations/2021_07_05_220855_create_municipios_table.php']);
        }
        if (! Schema::hasTable('categoria_vagas')) {
            $this->artisan('migrate', ['--path' => 'database/migrations/2021_07_05_220855_create_categoria_vagas_table.php']);
        }
        if (! Schema::hasColumn('vagas', 'cbo_id')) {
            $this->artisan('migrate', ['--path' => 'database/migrations/2026_04_27_100000_create_cbo_familias_table.php']);
            $this->artisan('migrate', ['--path' => 'database/migrations/2026_04_27_100001_create_cbos_table.php']);
            $this->artisan('migrate', ['--path' => 'database/migrations/2026_04_27_100002_add_cbo_id_to_vagas_table.php']);
        }
        if (! Schema::hasTable('activity_log')) {
            $this->artisan('migrate', ['--path' => 'database/migrations/2021_07_05_220855_create_activity_log_table.php']);
            $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_001500_add_batch_uuid_to_activity_log_table.php']);
            $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_002000_add_event_to_activity_log_table.php']);
        }
        if (! Schema::hasTable('cliente_configs')) {
            // cliente_configs tem FK para clientes, que não é migrada aqui (fora do escopo deste teste).
            Schema::disableForeignKeyConstraints();
            $this->artisan('migrate', ['--path' => 'database/migrations/2022_04_10_061731_create_cliente_configs_table.php']);
        }
        if (! Schema::hasColumn('cliente_configs', 'assinatura_digital_habilitada')) {
            $this->artisan('migrate', ['--path' => 'database/migrations/2026_02_28_000005_add_assinatura_digital_habilitada_to_cliente_configs_table.php']);
        }
        if (! Schema::hasColumn('cliente_configs', 'descricao_vaga_ia_habilitada')) {
            $this->artisan('migrate', ['--path' => 'database/migrations/2026_09_03_120000_add_descricao_vaga_ia_habilitada_to_cliente_configs_table.php']);
        }
    }

    private function habilitarIaParaEmpresa(int $empresaId): void
    {
        ClienteConfig::query()->updateOrCreate(
            ['cliente_id' => $empresaId],
            ['descricao_vaga_ia_habilitada' => true]
        );
    }

    private function registrarGates(): void
    {
        Gate::define('cadastro_vagas_abertas', fn () => true);
    }

    private function criarUsuarioEmpresa(): User
    {
        return new User([
            'nome' => 'Usuário Teste',
            'tipo' => 'ADMINISTRADOR',
            'ativo' => true,
            'temp' => false,
            'empresa_id' => 123,
        ]);
    }
}
