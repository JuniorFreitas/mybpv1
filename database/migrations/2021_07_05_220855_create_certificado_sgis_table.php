<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificadoSgisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificado_sgis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->index('certificado_sgis_cliente_id_foreign');
            $table->unsignedBigInteger('treinamento_evento_id')->index('certificado_sgis_treinamento_evento_id_foreign');
            $table->unsignedBigInteger('pessoa_evento_id')->index('certificado_sgis_pessoa_evento_id_foreign');
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
        Schema::dropIfExists('certificado_sgis');
    }
}
