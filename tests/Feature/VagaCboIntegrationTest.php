<?php

namespace Tests\Feature;

use App\Models\Cbo;
use App\Models\CboFamilia;
use App\Models\User;
use App\Models\Vaga;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VagaCboIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->garantirMigrations();
        $this->registrarGates();
        $this->withoutMiddleware();
    }

    public function testCadastraVagaComCboId(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);

        $cbo = $this->criarCboBase();

        $response = $this->post('/g/cadastro/vagas', [
            'nome' => 'Desenvolvedor PHP',
            'ativo' => 'true',
            'cbo_id' => $cbo->id,
            'vencimento_ids' => [],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('vagas', [
            'nome' => 'Desenvolvedor PHP',
            'empresa_id' => $user->empresa_id,
            'cbo_id' => $cbo->id,
        ]);
    }

    public function testAtualizaVagaComCboInvalidoRetornaErroDeValidacao(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);

        $cbo = $this->criarCboBase();

        $vaga = Vaga::query()->create([
            'nome' => 'Analista de Sistemas',
            'ativo' => true,
            'empresa_id' => $user->empresa_id,
            'cbo_id' => $cbo->id,
        ]);

        $response = $this->put("/g/cadastro/vagas/{$vaga->id}", [
            'nome' => 'Analista de Sistemas',
            'ativo' => 'true',
            'cbo_id' => 999999,
            'vencimento_ids' => [],
        ]);

        $response->assertStatus(400);
        $response->assertJsonStructure([
            'msg',
            'erros' => ['cbo_id'],
        ]);
    }

    public function testAutocompleteCboRetornaLabelComCodigoFamiliaEDescricaoSumaria(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);

        $this->criarCboBase();

        $response = $this->get('/g/autocomplete/cbos-ativos?busca=3171&rows=10');

        $response->assertOk();
        $response->assertJsonFragment(['codigo' => '317110']);
        $response->assertJsonFragment(['codigo_familia' => '3171']);
        $response->assertJsonFragment(['titulo' => 'Programador de sistemas']);
        $response->assertJsonFragment(['familia' => 'Técnicos de desenvolvimento']);
        $response->assertJsonFragment(['descricao_sumaria' => 'Desenvolvem sistemas.']);
        $response->assertJsonFragment([
            'label' => '317110 - Programador de sistemas - Técnicos de desenvolvimento',
        ]);
    }

    private function garantirMigrations(): void
    {
        if (! Schema::hasTable('vagas')) {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2021_07_05_220855_create_vagas_table.php',
            ]);
        }

        if (! Schema::hasTable('vagas_vencimentos')) {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_04_26_163500_create_vagas_vencimentos_table.php',
            ]);
        }

        if (! Schema::hasTable('activity_log')) {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2021_07_05_220855_create_activity_log_table.php',
            ]);
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_03_03_001500_add_batch_uuid_to_activity_log_table.php',
            ]);
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_03_03_002000_add_event_to_activity_log_table.php',
            ]);
        }

        if (! Schema::hasTable('cbo_familias')) {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_04_27_100000_create_cbo_familias_table.php',
            ]);
        }

        if (! Schema::hasTable('cbos')) {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_04_27_100001_create_cbos_table.php',
            ]);
        }

        if (! Schema::hasColumn('vagas', 'cbo_id')) {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_04_27_100002_add_cbo_id_to_vagas_table.php',
            ]);
        }
    }

    private function registrarGates(): void
    {
        Gate::define('cadastro_vagas', fn () => true);
        Gate::define('cadastro_vagas_insert', fn () => true);
        Gate::define('cadastro_vagas_update', fn () => true);
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

    private function criarCboBase(): Cbo
    {
        CboFamilia::query()->firstOrCreate(
            ['codigo' => '3171'],
            [
                'titulo' => 'Técnicos de desenvolvimento',
                'descricao_sumaria' => 'Desenvolvem sistemas.',
                'fonte' => 'Ministério do Trabalho e Emprego - CBO',
                'ativo' => true,
                'data_importacao' => now(),
            ]
        );

        return Cbo::query()->firstOrCreate(
            ['codigo' => '317110'],
            [
                'titulo' => 'Programador de sistemas',
                'codigo_familia' => '3171',
                'fonte' => 'Ministério do Trabalho e Emprego - CBO',
                'ativo' => true,
                'data_importacao' => now(),
            ]
        );
    }
}
