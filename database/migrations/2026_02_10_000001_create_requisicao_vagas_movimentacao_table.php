<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequisicaoVagasMovimentacaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requisicao_vagas_movimentacao', function (Blueprint $table) {
            // Chaves primárias e identificadores
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id')->index();

            // Relacionamentos
            $table->unsignedBigInteger('cliente_id')->nullable()->index();
            $table->unsignedBigInteger('centro_custo_id')->index();
            $table->unsignedInteger('cargo_id')->references('id')->on('vagas')->onDelete('restrict');
            $table->unsignedInteger('area_id')->references('id')->on('area_etiquetas')->onDelete('set null');

            // Dados específicos da requisição
            $table->integer('quantidade');
            $table->string('tipo_contratacao');
            $table->string('prioridade');
            $table->boolean('imediata')->default(false);
            $table->date('previsao_inicio')->nullable();
            $table->string('solicitante')->nullable();
            $table->longText('observacao')->nullable();

            // Quem criou/modificou
            $table->unsignedBigInteger('user_id')->index();

            // ===== APROVAÇÃO NÍVEL 1: GESTOR =====
            $table->unsignedBigInteger('user_aprovacao_id')->nullable()->index();
            $table->timestamp('data_aprovacao')->nullable();
            $table->text('obs_aprovacao')->nullable();
            $table->string('status_aprovacao')->nullable()->default('pendente');

            // ===== APROVAÇÃO NÍVEL 2: APROVAÇÃO EXTRA (Diretoria/Gerência) =====
            $table->unsignedBigInteger('aprovacao_extra_id')->nullable()->index();
            $table->timestamp('data_aprovacao_extra')->nullable();
            $table->text('obs_aprovacao_extra')->nullable();
            $table->string('status_aprovacao_extra')->nullable();

            // ===== APROVAÇÃO NÍVEL 3: RH (Final) =====
            $table->unsignedBigInteger('rh_aprovacao_id')->nullable()->index();
            $table->timestamp('data_aprovacao_rh')->nullable();
            $table->text('obs_rh')->nullable();
            $table->string('status_aprovacao_rh')->nullable();

            // Metadados
            $table->boolean('aprovado_via_script')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('empresa_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('set null');
            $table->foreign('centro_custo_id')->references('id')->on('centro_custos')->onDelete('restrict');
            // FK para cargo_id e area_id serão adicionadas em migration separada após validação de tipos
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('user_aprovacao_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('aprovacao_extra_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rh_aprovacao_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requisicao_vagas_movimentacao');
    }
}
