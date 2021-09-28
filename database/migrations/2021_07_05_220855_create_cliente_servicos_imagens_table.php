<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteServicosImagensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente_servicos_imagens', function (Blueprint $table) {
            $table->unsignedBigInteger('servicos_cliente_id')->index('cliente_servicos_imagens_servicos_cliente_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('cliente_servicos_imagens_arquivo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cliente_servicos_imagens');
    }
}
