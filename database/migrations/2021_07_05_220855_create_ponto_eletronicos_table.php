<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePontoEletronicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ponto_eletronicos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('autenticacao', 40);
            $table->unsignedBigInteger('funcionario_id')->index('ponto_eletronicos_funcionario_id_foreign');
            $table->unsignedBigInteger('escala_id')->index('ponto_eletronicos_escala_id_foreign');
            $table->unsignedBigInteger('jornada_id')->index('ponto_eletronicos_jornada_id_foreign');
            $table->unsignedBigInteger('ocorrencia_jornada_id')->index('ponto_eletronicos_ocorrencia_jornada_id_foreign');
            $table->unsignedBigInteger('periodo_id')->index('ponto_eletronicos_periodo_id_foreign');
            $table->boolean('facial')->default(0);
            $table->double('lat')->nullable();
            $table->double('long')->nullable();
            $table->text('justificativa')->nullable();
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
        Schema::dropIfExists('ponto_eletronicos');
    }
}
