<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ata_reuniao_acaos', function (Blueprint $table) {
            if (!Schema::hasColumn('ata_reuniao_acaos', 'empresa_id')) {
                $table->unsignedBigInteger('empresa_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('ata_reuniao_acaos', 'titulo')) {
                $table->string('titulo')->nullable()->after('ata_reuniao_id');
            }

            if (!Schema::hasColumn('ata_reuniao_acaos', 'descricao')) {
                $table->longText('descricao')->nullable()->after('titulo');
            }

            if (!Schema::hasColumn('ata_reuniao_acaos', 'responsavel_id')) {
                $table->unsignedBigInteger('responsavel_id')->nullable()->after('responsavel');
            }

            if (!Schema::hasColumn('ata_reuniao_acaos', 'criado_por')) {
                $table->unsignedBigInteger('criado_por')->nullable()->after('responsavel_id');
            }

            if (!Schema::hasColumn('ata_reuniao_acaos', 'prioridade')) {
                $table->string('prioridade', 20)->default('media')->after('status');
            }

            if (!Schema::hasColumn('ata_reuniao_acaos', 'percentual_conclusao')) {
                $table->unsignedTinyInteger('percentual_conclusao')->default(0)->after('prioridade');
            }

            if (!Schema::hasColumn('ata_reuniao_acaos', 'evidencia_esperada')) {
                $table->text('evidencia_esperada')->nullable()->after('percentual_conclusao');
            }

            if (!Schema::hasColumn('ata_reuniao_acaos', 'data_conclusao')) {
                $table->timestamp('data_conclusao')->nullable()->after('evidencia_esperada');
            }

            if (!Schema::hasColumn('ata_reuniao_acaos', 'validador_id')) {
                $table->unsignedBigInteger('validador_id')->nullable()->after('data_conclusao');
            }

            if (!Schema::hasColumn('ata_reuniao_acaos', 'validado_em')) {
                $table->timestamp('validado_em')->nullable()->after('validador_id');
            }

            if (!Schema::hasColumn('ata_reuniao_acaos', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (!Schema::hasColumn('ata_reuniao_acaos', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }

            if (!Schema::hasColumn('ata_reuniao_acaos', 'deleted_at')) {
                $table->softDeletes();
            }

            $table->index(['empresa_id', 'status', 'prazo', 'responsavel_id'], 'ata_acao_emp_status_prazo_resp_idx');
            $table->index(['empresa_id', 'responsavel_id', 'status', 'prazo'], 'ata_acao_emp_resp_status_prazo_idx');
            $table->index(['empresa_id', 'ata_reuniao_id', 'status'], 'ata_acao_emp_ata_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ata_reuniao_acaos', function (Blueprint $table) {
            $table->dropIndex('ata_acao_emp_status_prazo_resp_idx');
            $table->dropIndex('ata_acao_emp_resp_status_prazo_idx');
            $table->dropIndex('ata_acao_emp_ata_status_idx');

            $table->dropColumn([
                'empresa_id',
                'titulo',
                'descricao',
                'responsavel_id',
                'criado_por',
                'prioridade',
                'percentual_conclusao',
                'evidencia_esperada',
                'data_conclusao',
                'validador_id',
                'validado_em',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);
        });
    }
};
