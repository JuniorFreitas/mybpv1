<?php

namespace Tests\Feature;

use App\Models\Cbo;
use App\Models\CboFamilia;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CboApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        putenv('X_API_TOKEN=test-api-token-phpunit');
        $_SERVER['X_API_TOKEN'] = 'test-api-token-phpunit';
        $_ENV['X_API_TOKEN'] = 'test-api-token-phpunit';

        if (! Schema::hasTable('cbo_familias')) {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_04_27_100000_create_cbo_familias_table.php',
            ]);
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_04_27_100001_create_cbos_table.php',
            ]);
        }
    }

    public function testIndexRetornaCboComFamiliaEDescricao(): void
    {
        CboFamilia::query()->create([
            'codigo' => '3171',
            'titulo' => 'Técnicos de desenvolvimento',
            'descricao_sumaria' => 'Desenvolvem sistemas.',
            'fonte' => 'Ministério do Trabalho e Emprego - CBO',
            'ativo' => true,
            'data_importacao' => now(),
        ]);

        Cbo::query()->create([
            'codigo' => '317110',
            'titulo' => 'Programador de sistemas',
            'codigo_familia' => '3171',
            'fonte' => 'Ministério do Trabalho e Emprego - CBO',
            'ativo' => true,
            'data_importacao' => now(),
        ]);

        $response = $this->withHeader('X-API-TOKEN', 'test-api-token-phpunit')
            ->getJson('/api/cbos?q=programador');

        $response->assertOk();
        $response->assertJsonFragment(['codigo' => '317110']);
        $response->assertJsonFragment(['familia' => 'Técnicos de desenvolvimento']);
        $response->assertJsonFragment(['descricao_sumaria' => 'Desenvolvem sistemas.']);
    }

    public function testShowRetorna404QuandoInexistente(): void
    {
        $response = $this->withHeader('X-API-TOKEN', 'test-api-token-phpunit')
            ->getJson('/api/cbos/999999');

        $response->assertStatus(404);
    }

    public function testShowRetornaRegistro(): void
    {
        CboFamilia::query()->create([
            'codigo' => '3171',
            'titulo' => 'Família teste',
            'descricao_sumaria' => null,
            'fonte' => 'x',
            'ativo' => true,
            'data_importacao' => now(),
        ]);

        Cbo::query()->create([
            'codigo' => '317110',
            'titulo' => 'Programador',
            'codigo_familia' => '3171',
            'fonte' => 'x',
            'ativo' => true,
            'data_importacao' => now(),
        ]);

        $response = $this->withHeader('X-API-TOKEN', 'test-api-token-phpunit')
            ->getJson('/api/cbos/317110');

        $response->assertOk();
        $response->assertJsonPath('codigo', '317110');
        $response->assertJsonPath('familia', 'Família teste');
    }
}
