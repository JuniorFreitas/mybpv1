<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequisicaoVagasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requisicao_vagas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cliente_id')->index('requisicao_vagas_cliente_id_foreign');
            $table->unsignedBigInteger('centro_custo_id')->index('requisicao_vagas_centro_custo_id_foreign');
            $table->unsignedBigInteger('cargo_id')->index('requisicao_vagas_cargo_id_foreign');
            $table->unsignedBigInteger('area_id')->nullable()->index('requisicao_vagas_area_id_foreign');
            $table->integer('quantidade');
            $table->string('tipo_contratacao');
            $table->string('prioridade');
            $table->boolean('imediata');
            $table->date('previsao_inicio')->nullable();
            $table->string('solicitante')->nullable();
            $table->unsignedBigInteger('user_id')->index('requisicao_vagas_user_id_foreign');
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requisicao_vagas');
    }
}
