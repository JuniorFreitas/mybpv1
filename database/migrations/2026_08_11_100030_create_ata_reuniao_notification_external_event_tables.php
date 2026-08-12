<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ata_reuniao_notificacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->unsignedBigInteger('ata_reuniao_acao_id')->nullable();
            $table->unsignedBigInteger('destinatario_id')->nullable();
            $table->string('canal', 20)->default('email');
            $table->string('tipo', 40);
            $table->string('modo_disparo', 20)->default('automatico');
            $table->string('status', 20)->default('pendente');
            $table->date('data_prazo_referencia')->nullable();
            $table->string('destinatario_nome')->nullable();
            $table->string('destinatario_email')->nullable();
            $table->string('assunto')->nullable();
            $table->json('payload')->nullable();
            $table->text('erro')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();

            $table->unique([
                'empresa_id',
                'ata_reuniao_acao_id',
                'destinatario_id',
                'tipo',
                'data_prazo_referencia',
            ], 'ata_notif_d2_unique');
            $table->index(['empresa_id', 'status', 'tipo', 'data_prazo_referencia'], 'ata_notif_emp_status_tipo_prazo_idx');
            $table->index(['empresa_id', 'ata_reuniao_id', 'created_at'], 'ata_notif_emp_ata_data_idx');
        });

        Schema::create('ata_reuniao_compartilhamentos_externos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->string('token_hash', 128);
            $table->string('nome_externo')->nullable();
            $table->string('email_externo')->nullable();
            $table->string('escopo', 40)->default('leitura');
            $table->unsignedBigInteger('criado_por')->nullable();
            $table->timestamp('expira_em');
            $table->timestamp('revogado_em')->nullable();
            $table->timestamp('ultimo_acesso_em')->nullable();
            $table->timestamps();

            $table->unique('token_hash', 'ata_comp_token_unique');
            $table->index(['empresa_id', 'ata_reuniao_id', 'created_at'], 'ata_comp_emp_ata_data_idx');
            $table->index(['expira_em', 'revogado_em'], 'ata_comp_expira_revogado_idx');
        });

        Schema::create('ata_reuniao_eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->unsignedBigInteger('ator_id')->nullable();
            $table->string('ator_tipo', 40)->default('user');
            $table->string('tipo_evento', 80);
            $table->string('entidade_tipo', 80)->nullable();
            $table->unsignedBigInteger('entidade_id')->nullable();
            $table->json('dados')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['empresa_id', 'ata_reuniao_id', 'created_at'], 'ata_event_emp_ata_data_idx');
            $table->index(['empresa_id', 'tipo_evento', 'created_at'], 'ata_event_emp_tipo_data_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ata_reuniao_eventos');
        Schema::dropIfExists('ata_reuniao_compartilhamentos_externos');
        Schema::dropIfExists('ata_reuniao_notificacoes');
    }
};
