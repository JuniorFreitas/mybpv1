<?php

namespace Tests\Unit\Console;

use App\Models\Cbo;
use App\Models\CboFamilia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportarCboCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.cbo.familias_csv_url' => 'https://cbo.test/familias.csv',
            'services.cbo.ocupacoes_csv_url' => 'https://cbo.test/ocupacoes.csv',
            'services.cbo.perfil_csv_url' => 'https://cbo.test/perfil.csv',
        ]);

        if (! Schema::hasTable('cbo_familias')) {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_04_27_100000_create_cbo_familias_table.php',
            ]);
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_04_27_100001_create_cbos_table.php',
            ]);
        }
    }

    public function testImportarPersisteFamiliasOcupacoesEPerfil(): void
    {
        Http::fake([
            'https://cbo.test/familias.csv' => Http::response(
                "CODIGO;TITULO_FAMILIA\n3171;Família de desenvolvimento\n",
                200,
                ['Content-Type' => 'text/csv']
            ),
            'https://cbo.test/ocupacoes.csv' => Http::response(
                "COD_OCUPACAO;TITULO_OCUPACAO;COD_FAMILIA\n317110;Programador de sistemas;3171\n",
                200,
                ['Content-Type' => 'text/csv']
            ),
            'https://cbo.test/perfil.csv' => Http::response(
                "cod_familia;perfil_ocupacional\n3171;Desenvolvem sistemas e aplicações.\n",
                200,
                ['Content-Type' => 'text/csv']
            ),
        ]);

        $this->artisan('cbo:importar', ['--force' => true])->assertSuccessful();

        $this->assertDatabaseHas('cbo_familias', [
            'codigo' => '3171',
            'titulo' => 'Família de desenvolvimento',
        ]);

        $this->assertDatabaseHas('cbos', [
            'codigo' => '317110',
            'titulo' => 'Programador de sistemas',
            'codigo_familia' => '3171',
        ]);

        $familia = CboFamilia::query()->where('codigo', '3171')->first();
        $this->assertNotNull($familia);
        $this->assertStringContainsString('Desenvolvem sistemas', (string) $familia->descricao_sumaria);

        $cbo = Cbo::query()->where('codigo', '317110')->first();
        $this->assertNotNull($cbo);
    }

    public function testImportarAceitaFormatoPerfilOcupacionalGovBr(): void
    {
        Http::fake([
            'https://cbo.test/familias.csv' => Http::response(
                "COD_FAMILIA;TITULO_FAMILIA\n0201;Família teste\n",
                200,
                ['Content-Type' => 'text/csv']
            ),
            'https://cbo.test/ocupacoes.csv' => Http::response(
                implode("\n", [
                    'COD_GRANDE_GRUPO;COD_SUBGRUPO_PRINCIPAL;COD_SUBGRUPO;COD_FAMILIA;COD_OCUPACAO;SGL_GRANDE_AREA;NOME_GRANDE_AREA;COD_ATIVIDADE;NOME_ATIVIDADE',
                    '0;02;020;0201;020105;A;Título oficial da ocupação;1;Atividade A',
                    '0;02;020;0201;020105;A;Título oficial da ocupação;2;Atividade B',
                    '0;02;020;0201;020105;A;Outro rótulo;3;Atividade C',
                ]) . "\n",
                200,
                ['Content-Type' => 'text/csv']
            ),
        ]);

        config(['services.cbo.perfil_csv_url' => null]);

        $this->artisan('cbo:importar', ['--force' => true])->assertSuccessful();

        $this->assertDatabaseHas('cbos', [
            'codigo' => '020105',
            'titulo' => 'Título oficial da ocupação',
            'codigo_familia' => '0201',
        ]);
    }
}
