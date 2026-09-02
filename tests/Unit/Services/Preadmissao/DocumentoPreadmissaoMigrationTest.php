<?php

namespace Tests\Unit\Services\Preadmissao;

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class DocumentoPreadmissaoMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('documentos_curriculos_adm_empresa');
        Schema::dropIfExists('documentos_curriculos_cat_adm_empresa');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('users');

        Schema::create('documentos_curriculos_cat_adm_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('label');
        });
        Schema::create('documentos_curriculos_adm_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('tipo');
        });
    }

    public function test_migration_cria_constraint_unica_por_empresa_e_tipo(): void
    {
        DB::table('documentos_curriculos_adm_empresa')->insert([
            'empresa_id' => 10,
            'tipo' => 'rg',
        ]);

        $this->migration()->up();

        $this->expectException(QueryException::class);
        DB::table('documentos_curriculos_adm_empresa')->insert([
            'empresa_id' => 10,
            'tipo' => 'rg',
        ]);
    }

    public function test_migration_recusa_dados_duplicados_sem_excluir_registros(): void
    {
        DB::table('documentos_curriculos_adm_empresa')->insert([
            ['empresa_id' => 10, 'tipo' => 'rg'],
            ['empresa_id' => 10, 'tipo' => 'rg'],
        ]);

        try {
            $this->migration()->up();
            $this->fail('A migration deveria recusar tipos duplicados existentes.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('empresa 10', $e->getMessage());
            $this->assertSame(2, DB::table('documentos_curriculos_adm_empresa')->count());
        }
    }

    public function test_migration_cria_constraint_unica_para_categoria_da_empresa(): void
    {
        DB::table('documentos_curriculos_cat_adm_empresa')->insert([
            'empresa_id' => 10,
            'label' => 'PESSOAIS',
        ]);

        $this->migration()->up();

        $this->expectException(QueryException::class);
        DB::table('documentos_curriculos_cat_adm_empresa')->insert([
            'empresa_id' => 10,
            'label' => 'PESSOAIS',
        ]);
    }

    public function test_migration_corrige_foreign_keys_de_empresa_para_clientes(): void
    {
        Schema::drop('documentos_curriculos_adm_empresa');
        Schema::drop('documentos_curriculos_cat_adm_empresa');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
        });
        Schema::create('documentos_curriculos_cat_adm_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('label');
            $table->foreign('empresa_id')->references('id')->on('users');
        });
        Schema::create('documentos_curriculos_adm_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('tipo');
            $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('categoria_id')->references('id')->on('documentos_curriculos_cat_adm_empresa');
        });
        DB::table('clientes')->insert(['id' => 10]);

        $this->foreignKeyMigration()->up();

        DB::table('documentos_curriculos_cat_adm_empresa')->insert([
            'id' => 1,
            'empresa_id' => 10,
            'label' => 'PESSOAIS',
        ]);
        DB::table('documentos_curriculos_adm_empresa')->insert([
            'empresa_id' => 10,
            'categoria_id' => 1,
            'tipo' => 'rg',
        ]);

        $this->expectException(QueryException::class);
        DB::table('documentos_curriculos_cat_adm_empresa')->insert([
            'empresa_id' => 99,
            'label' => 'SEM CLIENTE',
        ]);
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_09_01_000950_garantir_integridade_documentos_preadmissao.php');
    }

    private function foreignKeyMigration(): object
    {
        return require database_path('migrations/2026_09_01_000900_corrigir_foreign_keys_documentos_preadmissao.php');
    }
}
