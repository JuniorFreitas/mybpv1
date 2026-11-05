<?php

namespace Tests\Unit\Domain\SaudeOcupacional;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema mínimo (SQLite :memory:) das tabelas usadas pelo módulo Saúde Ocupacional.
 * Tabelas do módulo vêm das próprias migrations.
 */
trait CriaTabelasSaudeOcupacional
{
    protected function criarTabelasSaude(): void
    {
        $tabelas = [
            'pcmsos_versoes_exames', 'pcmsos_versoes', 'exame_riscos_exames', 'pcmsos', 'exames',
            'pgr_inventarios_riscos', 'pgr_inventarios_cargos', 'pgr_inventarios', 'saude_medicos_vinculos', 'saude_medicos',
            'saude_regras_historicos', 'saude_regras', 'activity_log', 'examesesmts', 'exame_funcionarios',
            'empresas_exame_tipos_periodicidades', 'exame_tipos', 'exame_riscos', 'exame_risco_tipos', 'empresa_exames',
            'demissaos', 'admissoes', 'feedback_curriculos', 'curriculos', 'vagas_abertas', 'vagas', 'cliente_configs', 'users',
        ];
        foreach ($tabelas as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id')->nullable();
            $t->string('nome')->nullable();
            $t->string('email')->nullable();
            $t->string('tipo')->nullable();
            $t->boolean('ativo')->default(true);
            $t->boolean('require_password_reset')->default(false);
            $t->unsignedInteger('password_reset_days')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('cliente_configs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('cliente_id');
            $t->boolean('modulo_saude_ocupacional')->default(false);
            $t->unsignedInteger('vencimento_aso')->nullable();
            $t->json('saude_alerta_janelas')->nullable();
            $t->boolean('saude_resumo_semanal')->default(true);
        });
        Schema::create('vagas', function (Blueprint $t) {
            $t->id();
            $t->string('nome');
            $t->boolean('ativo')->default(true);
            $t->unsignedBigInteger('empresa_id');
        });
        Schema::create('vagas_abertas', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('vaga_id');
            $t->unsignedBigInteger('empresa_id');
        });
        Schema::create('curriculos', function (Blueprint $t) {
            $t->id();
            $t->string('nome');
            $t->date('nascimento')->nullable();
            $t->string('cpf', 14)->nullable();
            $t->string('email')->nullable();
            $t->softDeletes();
        });
        Schema::dropIfExists('controle_exame_sesmt_anexos');
        Schema::create('controle_exame_sesmt_anexos', function (Blueprint $t) {
            $t->unsignedBigInteger('arquivo_id');
            $t->unsignedBigInteger('examesesmt_id');
        });
        Schema::dropIfExists('documento_para_assinatura');
        Schema::create('documento_para_assinatura', function (Blueprint $t) {
            $t->id();
            $t->string('status')->nullable();
        });
        Schema::create('feedback_curriculos', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('curriculo_id');
            $t->unsignedBigInteger('vaga_id')->nullable();
            $t->unsignedBigInteger('vagas_abertas_id')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('admissoes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('feedback_id');
            $t->string('status');
            $t->string('cargo')->nullable();
            $t->date('data_admissao')->nullable();
            $t->softDeletes();
        });
        Schema::create('demissaos', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('feedback_id');
        });
        Schema::create('empresa_exames', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->string('nome');
            $t->boolean('ativo')->default(true);
        });
        Schema::create('exame_risco_tipos', function (Blueprint $t) {
            $t->id();
            $t->string('label');
            $t->boolean('ativo')->default(true);
            $t->timestamps();
        });
        Schema::create('exame_riscos', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('risco_tipo');
            $t->string('label');
            $t->boolean('ativo')->default(true);
            $t->timestamps();
        });
        Schema::create('exame_tipos', function (Blueprint $t) {
            $t->id();
            $t->string('label');
            $t->boolean('ativo')->default(true);
            $t->timestamps();
        });
        Schema::create('empresas_exame_tipos_periodicidades', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('exame_tipo_id');
            $t->string('unidade', 10);
            $t->unsignedInteger('valor');
            $t->timestamps();
        });
        Schema::create('exame_funcionarios', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('exame_tipo_id')->nullable();
            $t->unsignedBigInteger('feedback_id')->nullable();
            $t->boolean('pcmso')->default(false);
            $t->unsignedBigInteger('pcmso_id')->nullable();
            $t->unsignedBigInteger('formulario_id')->nullable();
            $t->unsignedBigInteger('empresa_exame_id')->nullable();
            $t->unsignedBigInteger('user_encaminhou_id')->nullable();
            $t->string('token')->nullable();
            $t->text('respostas')->nullable();
            $t->date('encaminhamento_data')->nullable();
            $t->timestamps();
        });
        Schema::dropIfExists('formularios');
        Schema::create('formularios', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->string('titulo');
        });
        Schema::create('pcmsos', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->string('label');
            $t->boolean('ativo')->default(true);
        });
        Schema::create('exames', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('exame_tipo_id')->nullable();
            $t->string('label');
            $t->string('codigo_esocial', 10)->nullable();
            $t->boolean('ativo')->default(true);
            $t->timestamps();
        });
        Schema::create('examesesmts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('exame_funcionario_id');
            $t->unsignedBigInteger('feedback_id')->nullable();
            $t->boolean('exame_realizado')->default(false);
            $t->json('resultado')->nullable();
            $t->date('data_realizacao')->nullable();
            $t->date('data_vencimento')->nullable();
            $t->boolean('vencido')->default(false);
            $t->boolean('atual')->default(false);
            $t->timestamps();
        });
        Schema::create('activity_log', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('log_name')->nullable();
            $t->text('description');
            $t->text('descricao');
            $t->nullableMorphs('subject', 'subject');
            $t->nullableMorphs('causer', 'causer');
            $t->json('properties')->nullable();
            $t->uuid('batch_uuid')->nullable();
            $t->string('event')->nullable();
            $t->timestamps();
        });

        foreach ([
            '2026_11_05_110000_create_saude_regras_table.php',
            '2026_11_05_110001_create_saude_regras_historicos_table.php',
            '2026_11_05_120000_create_saude_medicos_table.php',
            '2026_11_05_120001_create_saude_medicos_vinculos_table.php',
            '2026_11_05_120002_create_pgr_inventarios_tables.php',
            '2026_11_05_130001_create_exame_riscos_exames_table.php',
            '2026_11_05_130002_create_pcmsos_versoes_tables.php',
            '2026_11_05_150000_create_examesesmts_aso_table.php',
            '2026_11_05_160000_create_saude_documentos_tables.php',
        ] as $migration) {
            (require database_path('migrations/' . $migration))->up();
        }
    }
}
