<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParecerRotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parecer_rotas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('parecer_rotas_feedback_id_foreign');
            $table->boolean('tem_rota')->nullable();
            $table->string('qual')->nullable();
            $table->string('bairro_rota')->nullable();
            $table->string('ponto_referencia_rota')->nullable();
            $table->string('bairro_residencia')->nullable();
            $table->string('ponto_referencia_residencia')->nullable();
            $table->boolean('pega_onibus')->nullable();
            $table->string('pega_onibus_qual_ponto')->nullable();
            $table->boolean('vale_transporte')->nullable();
            $table->boolean('rota_disponivel_turno_a')->nullable();
            $table->boolean('rota_disponivel_turno_b')->nullable();
            $table->boolean('rota_disponivel_turno_c')->nullable();
            $table->boolean('rota_disponivel_turno_o')->nullable();
            $table->string('rota_disponivel_outros')->nullable();
            $table->boolean('rota_atende')->nullable();
            $table->string('rota_tipo')->nullable();
            $table->unsignedBigInteger('aprovado_por')->nullable()->index('parecer_rotas_aprovado_por_foreign');
            $table->string('quem_entrevistou')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('formulario_id')->nullable()->index('parecer_rotas_formulario_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parecer_rotas');
    }
}
