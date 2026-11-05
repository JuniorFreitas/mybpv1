<?php

namespace Tests\Unit\Domain\SaudeOcupacional;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HabilidadesSaudeOcupacionalMigrationTest extends TestCase
{
    private object $migration;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('papeis_habilidades');
        Schema::dropIfExists('habilidades');
        Schema::create('habilidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('descricao')->nullable();
        });
        Schema::create('papeis_habilidades', function (Blueprint $table) {
            $table->unsignedBigInteger('papel_id');
            $table->unsignedBigInteger('habilidade_id');
        });

        $this->migration = require database_path('migrations/2026_11_05_100001_add_saude_ocupacional_habilidades.php');
    }

    public function test_libera_rh_e_configuracao_mas_nunca_dados_sensiveis(): void
    {
        $exames = DB::table('habilidades')->insertGetId(['nome' => 'admissao_controle_exames']);
        $cadastro = DB::table('habilidades')->insertGetId(['nome' => 'cadastro_empresa_exame']);
        DB::table('papeis_habilidades')->insert([
            ['papel_id' => 1, 'habilidade_id' => $exames],   // RH operacional
            ['papel_id' => 2, 'habilidade_id' => $cadastro], // quem configura exames
        ]);

        $this->migration->up();
        $this->migration->up(); // idempotente

        $this->assertSame([1, 2], $this->papeisCom('saude_ocupacional'));
        $this->assertSame([2], $this->papeisCom('saude_ocupacional_configuracao'));
        $this->assertSame([], $this->papeisCom('saude_ocupacional_dados_sensiveis'));
        $this->assertSame(1, DB::table('habilidades')->where('nome', 'saude_ocupacional')->count());
    }

    public function test_down_remove_habilidades_e_vinculos(): void
    {
        $this->migration->up();
        $this->migration->down();

        $this->assertSame(0, DB::table('habilidades')->where('nome', 'like', 'saude_ocupacional%')->count());
        $this->assertSame(0, DB::table('papeis_habilidades')->count());
    }

    /** @return list<int> */
    private function papeisCom(string $habilidade): array
    {
        return DB::table('papeis_habilidades')
            ->join('habilidades', 'habilidades.id', '=', 'papeis_habilidades.habilidade_id')
            ->where('habilidades.nome', $habilidade)
            ->orderBy('papel_id')
            ->pluck('papel_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
