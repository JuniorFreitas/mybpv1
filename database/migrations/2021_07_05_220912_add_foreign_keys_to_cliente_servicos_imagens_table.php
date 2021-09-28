<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToClienteServicosImagensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cliente_servicos_imagens', function (Blueprint $table) {
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('servicos_cliente_id')->references('id')->on('servicos_clientes')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cliente_servicos_imagens', function (Blueprint $table) {
            $table->dropForeign('cliente_servicos_imagens_arquivo_id_foreign');
            $table->dropForeign('cliente_servicos_imagens_servicos_cliente_id_foreign');
        });
    }
}
