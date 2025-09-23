<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecrutamentoHistoricosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recrutamento_historicos', function (Blueprint $table) {
            $table->id();
            
            // Referências principais
            $table->unsignedBigInteger('curriculo_id');
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('user_id');
            
            // Informações da ação
            $table->string('acao', 100); // 'criado', 'atualizado', 'selecionado', 'rejeitado', 'telefone_adicionado', 'telefone_removido', etc.
            $table->string('modulo', 50); // 'curriculo', 'feedback', 'telefone', 'documento', etc.
            $table->text('descricao')->nullable(); // Descrição detalhada da ação
            
            // Dados antes e depois da mudança (JSON)
            $table->json('dados_anteriores')->nullable();
            $table->json('dados_novos')->nullable();
            $table->json('request_completo')->nullable(); // Todo o request que gerou a mudança
            
            // Metadados
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('session_id')->nullable();
            
            // Timestamps
            $table->timestamp('data_acao')->useCurrent();
            $table->timestamps();
            
            // Índices
            $table->index(['curriculo_id', 'data_acao']);
            $table->index(['empresa_id', 'data_acao']);
            $table->index(['user_id', 'data_acao']);
            $table->index(['acao', 'data_acao']);
            $table->index('modulo');
            
            // Foreign keys
            $table->foreign('curriculo_id')->references('id')->on('curriculos')->onDelete('cascade');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onDelete('set null');
            $table->foreign('empresa_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recrutamento_historicos');
    }
}
