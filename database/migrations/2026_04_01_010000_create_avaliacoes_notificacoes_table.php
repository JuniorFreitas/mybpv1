<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avaliacoes_notificacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('avaliacao_id');
            $table->unsignedBigInteger('avaliacao_feedback_id');
            $table->unsignedBigInteger('funcionario_id');
            $table->unsignedBigInteger('avaliador_id');
            $table->unsignedBigInteger('usuario_solicitante_id')->nullable();
            $table->string('canal', 20)->default('email');
            $table->string('modo_disparo', 20);
            $table->string('tipo', 40);
            $table->string('status', 20)->default('pendente');
            $table->string('destinatario_nome');
            $table->string('destinatario_email')->nullable();
            $table->string('destinatario_telefone')->nullable();
            $table->string('assunto')->nullable();
            $table->json('payload')->nullable();
            $table->text('erro')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'avaliacao_id'], 'idx_av_notif_empresa_avaliacao');
            $table->index(['avaliacao_feedback_id', 'tipo'], 'idx_av_notif_feedback_tipo');
            $table->index(['status', 'canal'], 'idx_av_notif_status_canal');
            $table->index(['usuario_solicitante_id'], 'idx_av_notif_usuario_solicitante');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avaliacoes_notificacoes');
    }
};
