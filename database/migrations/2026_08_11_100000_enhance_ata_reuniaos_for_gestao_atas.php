<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ata_reuniaos', function (Blueprint $table) {
            if (!Schema::hasColumn('ata_reuniaos', 'codigo')) {
                $table->string('codigo', 40)->nullable()->after('id');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'uuid_publico')) {
                $table->uuid('uuid_publico')->nullable()->after('codigo');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'titulo')) {
                $table->string('titulo')->nullable()->after('quem_cadastrou');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'objetivo')) {
                $table->text('objetivo')->nullable()->after('titulo');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'status')) {
                $table->string('status', 40)->default('rascunho')->after('objetivo');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'nivel_acesso')) {
                $table->string('nivel_acesso', 40)->default('privada')->after('status');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'classificacao_confidencialidade')) {
                $table->string('classificacao_confidencialidade', 40)->default('uso_interno')->after('nivel_acesso');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'organizador_id')) {
                $table->unsignedBigInteger('organizador_id')->nullable()->after('classificacao_confidencialidade');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'redator_id')) {
                $table->unsignedBigInteger('redator_id')->nullable()->after('organizador_id');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'aprovacao_modo')) {
                $table->string('aprovacao_modo', 30)->default('paralela')->after('redator_id');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'versao_atual')) {
                $table->string('versao_atual', 20)->default('0.1')->after('aprovacao_modo');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'timezone')) {
                $table->string('timezone', 80)->default('America/Sao_Paulo')->after('versao_atual');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'link_videoconferencia')) {
                $table->string('link_videoconferencia', 500)->nullable()->after('timezone');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'observacoes')) {
                $table->text('observacoes')->nullable()->after('link_videoconferencia');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'aprovada_em')) {
                $table->timestamp('aprovada_em')->nullable()->after('data_fim');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'publicada_em')) {
                $table->timestamp('publicada_em')->nullable()->after('aprovada_em');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'bloqueada_em')) {
                $table->timestamp('bloqueada_em')->nullable()->after('publicada_em');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'cancelada_em')) {
                $table->timestamp('cancelada_em')->nullable()->after('bloqueada_em');
            }

            if (!Schema::hasColumn('ata_reuniaos', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }

            $table->unique(['empresa_id', 'codigo'], 'ata_reuniao_emp_codigo_unique');
            $table->unique('uuid_publico', 'ata_reuniao_uuid_publico_unique');
            $table->index(['empresa_id', 'status', 'data_inicio'], 'ata_reuniao_emp_status_data_idx');
            $table->index(['empresa_id', 'organizador_id', 'status'], 'ata_reuniao_emp_org_status_idx');
            $table->index(['empresa_id', 'redator_id', 'status'], 'ata_reuniao_emp_red_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ata_reuniaos', function (Blueprint $table) {
            $table->dropUnique('ata_reuniao_emp_codigo_unique');
            $table->dropUnique('ata_reuniao_uuid_publico_unique');
            $table->dropIndex('ata_reuniao_emp_status_data_idx');
            $table->dropIndex('ata_reuniao_emp_org_status_idx');
            $table->dropIndex('ata_reuniao_emp_red_status_idx');

            $table->dropColumn([
                'codigo',
                'uuid_publico',
                'titulo',
                'objetivo',
                'status',
                'nivel_acesso',
                'classificacao_confidencialidade',
                'organizador_id',
                'redator_id',
                'aprovacao_modo',
                'versao_atual',
                'timezone',
                'link_videoconferencia',
                'observacoes',
                'aprovada_em',
                'publicada_em',
                'bloqueada_em',
                'cancelada_em',
                'deleted_at',
            ]);
        });
    }
};
