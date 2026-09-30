<?php

namespace Tests\Unit\Authorization;

use App\Authorization\HabilidadeAliasConsolidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HabilidadeAliasConsolidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->criarSchemaMinimo();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('papeis_habilidades');
        Schema::dropIfExists('papeis');
        Schema::dropIfExists('habilidades');
        parent::tearDown();
    }

    public function test_dry_run_merge_nao_altera_banco(): void
    {
        $canonId = $this->criarHabilidade('admissao_pos_admissao', 'canon');
        $aliasId = $this->criarHabilidade('pos_admissao', 'alias');

        $papelId = $this->criarPapel();
        DB::table('papeis_habilidades')->insert([
            'papel_id' => $papelId,
            'habilidade_id' => $aliasId,
        ]);

        $consolidator = new HabilidadeAliasConsolidator();
        $result = $consolidator->consolidatePair('pos_admissao', 'admissao_pos_admissao', false);

        $this->assertTrue($result['ok']);
        $this->assertSame('merge', $result['action']);
        $this->assertTrue(DB::table('habilidades')->where('nome', 'pos_admissao')->exists());
        $this->assertSame(1, DB::table('papeis_habilidades')->where('habilidade_id', $aliasId)->count());
        $this->assertSame(0, DB::table('papeis_habilidades')->where('habilidade_id', $canonId)->count());
    }

    public function test_apply_merge_preserva_pivots_no_canonico(): void
    {
        $canonId = $this->criarHabilidade('historico_dossie_insert', 'canon');
        $aliasId = $this->criarHabilidade('admissao_historico_dossie_insert', 'alias');

        $papelId = $this->criarPapel();
        DB::table('papeis_habilidades')->insert([
            'papel_id' => $papelId,
            'habilidade_id' => $aliasId,
        ]);

        $consolidator = new HabilidadeAliasConsolidator();
        $result = $consolidator->consolidatePair(
            'admissao_historico_dossie_insert',
            'historico_dossie_insert',
            true
        );

        $this->assertTrue($result['ok']);
        $this->assertFalse(DB::table('habilidades')->where('nome', 'admissao_historico_dossie_insert')->exists());
        $this->assertTrue(DB::table('habilidades')->where('nome', 'historico_dossie_insert')->exists());
        $this->assertSame(1, DB::table('papeis_habilidades')->where('habilidade_id', $canonId)->count());
    }

    public function test_apply_rename_quando_somente_alias(): void
    {
        $this->criarHabilidade('posadmissao', 'alias only');

        $consolidator = new HabilidadeAliasConsolidator();
        $result = $consolidator->consolidatePair('posadmissao', 'admissao_pos_admissao', true);

        $this->assertTrue($result['ok']);
        $this->assertSame('rename', $result['action']);
        $this->assertFalse(DB::table('habilidades')->where('nome', 'posadmissao')->exists());
        $this->assertTrue(DB::table('habilidades')->where('nome', 'admissao_pos_admissao')->exists());
    }

    private function criarSchemaMinimo(): void
    {
        Schema::create('habilidades', function ($table) {
            $table->id();
            $table->string('nome');
            $table->string('descricao')->nullable();
        });

        Schema::create('papeis', function ($table) {
            $table->id();
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('master')->default(false);
            $table->unsignedBigInteger('empresa_id')->nullable();
        });

        Schema::create('papeis_habilidades', function ($table) {
            $table->unsignedBigInteger('papel_id');
            $table->unsignedBigInteger('habilidade_id');
        });
    }

    private function criarHabilidade(string $nome, string $descricao): int
    {
        return (int) DB::table('habilidades')->insertGetId([
            'nome' => $nome,
            'descricao' => $descricao,
        ]);
    }

    private function criarPapel(): int
    {
        return (int) DB::table('papeis')->insertGetId([
            'nome' => 'Papel teste',
            'descricao' => 'test',
            'ativo' => true,
            'master' => false,
            'empresa_id' => 1,
        ]);
    }
}
