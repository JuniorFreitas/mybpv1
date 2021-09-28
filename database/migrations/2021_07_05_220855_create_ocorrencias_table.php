<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOcorrenciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ocorrencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->nullable()->index('ocorrencias_cliente_id_foreign');
            $table->unsignedBigInteger('usuario_id')->nullable()->index('ocorrencias_usuario_id_foreign');
            $table->unsignedBigInteger('setor_id')->nullable()->index('ocorrencias_setor_id_foreign');
            $table->string('assunto', 150);
            $table->unsignedBigInteger('quem_criou')->index('ocorrencias_quem_criou_foreign');
            $table->unsignedBigInteger('quem_atualizou')->nullable()->index('ocorrencias_quem_atualizou_foreign');
            $table->dateTime('datahora_finalizou')->nullable();
            $table->unsignedBigInteger('quem_finalizou')->nullable()->index('ocorrencias_quem_finalizou_foreign');
            $table->string('status');
            $table->string('tipo');
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
        Schema::dropIfExists('ocorrencias');
    }
}
